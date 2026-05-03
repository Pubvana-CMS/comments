<?php

declare(strict_types=1);

namespace Pubvana\Comments\Services;

use Enlivenapp\FlightSchool\Exception\ValidationException;
use Flight;
use Pubvana\Comments\Models\Comment;

class CommentService
{
    private Comment $model;

    private const CAPTCHA_ENDPOINTS = [
        'hcaptcha'  => 'https://api.hcaptcha.com/siteverify',
        'recaptcha' => 'https://www.google.com/recaptcha/api/siteverify',
    ];

    private const CAPTCHA_POST_FIELDS = [
        'hcaptcha'  => 'h-captcha-response',
        'recaptcha' => 'g-recaptcha-response',
    ];

    public function __construct(\PDO $pdo)
    {
        $this->model = new Comment($pdo);
    }

    /**
     * Read a Comments setting from the settings service.
     */
    private function setting(string $key): mixed
    {
        return Flight::app()->settings()->get('Comments.' . $key, 'self');
    }

    /**
     * Paginated comment list for admin, optionally filtered by status.
     */
    public function list(int $page = 1, int $perPage = 25, ?string $status = null): array
    {
        return $this->model->paginate($page, $perPage, $status);
    }

    /**
     * Get a threaded comment tree for a content item (public display).
     */
    public function findForContent(string $type, int $id): array
    {
        $comments = $this->model->findByContent($type, $id, 'approved');
        return $this->buildTree($comments);
    }

    /**
     * Find a single comment by ID.
     */
    public function find(int $id): ?Comment
    {
        return $this->model->findById($id);
    }

    /**
     * Create a new comment.
     *
     * Validates nesting depth, captcha, and sanitizes body.
     */
    public function create(array $data): Comment
    {
        // Validate nesting depth
        if (!empty($data['parent_id'])) {
            $depth = $this->model->getDepth((int) $data['parent_id']);
            $maxDepth = (int) ($this->setting('max_nesting_depth') ?? 3);

            if ($depth >= $maxDepth) {
                throw new ValidationException('Maximum comment nesting depth reached.');
            }
        }

        // Captcha verification
        if ($this->isCaptchaEnabled()) {
            $token = $data['captcha_token'] ?? '';
            $ip = $data['ip_address'] ?? '';

            if (empty($token) || !$this->verifyCaptcha($token, $ip)) {
                throw new ValidationException('Captcha verification failed.');
            }
        }

        // Remove non-column fields before insert
        unset($data['captcha_token']);

        // HTMLPurifier - always sanitize
        if (!empty($data['body'])) {
            $data['body'] = $this->purifyContent($data['body']);
        }

        // Set default status from settings
        if (empty($data['status'])) {
            $data['status'] = $this->setting('default_status') ?? 'pending';
        }

        $comment = $this->model->createRecord($data);

        // @todo: notification hook - future notifications package will listen here

        return $comment;
    }

    /**
     * Approve a comment.
     */
    public function approve(int $id): ?Comment
    {
        return $this->model->updateStatus($id, 'approved');
    }

    /**
     * Reject a comment.
     */
    public function reject(int $id): ?Comment
    {
        return $this->model->updateStatus($id, 'rejected');
    }

    /**
     * Hard delete a comment.
     */
    public function delete(int $id): bool
    {
        return $this->model->deleteById($id);
    }

    /**
     * Count comments by status (null = all).
     */
    public function countByStatus(?string $status = null): int
    {
        return $this->model->countByStatus($status);
    }

    /**
     * Whether the comment system is globally enabled.
     */
    public function isEnabled(): bool
    {
        return (bool) ($this->setting('comments_enabled') ?? true);
    }

    /**
     * Whether captcha is enabled in settings.
     */
    public function isCaptchaEnabled(): bool
    {
        $provider = $this->setting('captcha_provider') ?? 'none';
        return $provider !== 'none' && !empty($this->setting('captcha_site_key'));
    }

    /**
     * Get the captcha provider name.
     */
    public function getCaptchaProvider(): string
    {
        return $this->setting('captcha_provider') ?? 'none';
    }

    /**
     * Get the captcha site key.
     */
    public function getCaptchaSiteKey(): string
    {
        return $this->setting('captcha_site_key') ?? '';
    }

    /**
     * Get the POST field name for the current captcha provider.
     */
    public function getCaptchaPostField(): string
    {
        $provider = $this->getCaptchaProvider();
        return self::CAPTCHA_POST_FIELDS[$provider] ?? '';
    }

    /**
     * Whether guest comments are allowed.
     */
    public function allowsGuestComments(): bool
    {
        return (bool) ($this->setting('allow_guest_comments') ?? false);
    }

    /**
     * Verify a captcha token against the configured provider.
     */
    public function verifyCaptcha(string $token, string $remoteIp): bool
    {
        $provider = $this->setting('captcha_provider') ?? 'none';

        if ($provider === 'none' || !isset(self::CAPTCHA_ENDPOINTS[$provider])) {
            return true;
        }

        $secret = $this->setting('captcha_secret_key') ?? '';

        if (empty($secret)) {
            return false;
        }

        $postData = http_build_query([
            'secret'   => $secret,
            'response' => $token,
            'remoteip' => $remoteIp,
        ]);

        $context = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $postData,
                'timeout' => 10,
            ],
        ]);

        $response = @file_get_contents(self::CAPTCHA_ENDPOINTS[$provider], false, $context);

        if ($response === false) {
            return false;
        }

        $result = json_decode($response, true);

        return isset($result['success']) && $result['success'] === true;
    }

    /**
     * Sanitize HTML content via HTMLPurifier.
     */
    private function purifyContent(string $html): string
    {
        $config = \HTMLPurifier_Config::create(Flight::get('html_purifier') ?? []);
        return (new \HTMLPurifier($config))->purify($html);
    }

    /**
     * Build a nested tree from a flat array of comments.
     */
    private function buildTree(array $comments): array
    {
        $map = [];
        $tree = [];

        // Index all comments by ID
        foreach ($comments as $comment) {
            $map[$comment->id] = $comment;
            $comment->setCustomData('children', []);
        }

        // Build the tree
        foreach ($comments as $comment) {
            if ($comment->parent_id !== null && isset($map[$comment->parent_id])) {
                $parent = $map[$comment->parent_id];
                $children = $parent->children;
                $children[] = $comment;
                $parent->setCustomData('children', $children);
            } else {
                $tree[] = $comment;
            }
        }

        return $tree;
    }
}
