<?php

declare(strict_types=1);

namespace Pubvana\Comments\Controllers;

use Enlivenapp\FlightSchool\Exception\ValidationException;
use flight\Engine;

class CommentsController
{
    protected Engine $app;
    protected array $config;

    public function __construct(Engine $app, string $configPrepend)
    {
        $this->app    = $app;
        $this->config = $app->get($configPrepend) ?? [];
    }

    /**
     * Display comments for a content item.
     */
    public function index(string $type, string $id): void
    {
        $comments = $this->app->comments()->findForContent($type, (int) $id);

        $this->app->render('comments/index', [
            'comments'       => $comments,
            'commentableType' => $type,
            'commentableId'  => (int) $id,
        ]);
    }

    /**
     * Handle comment submission.
     */
    public function store(string $type, string $id): void
    {
        $referrer = $this->app->request()->referrer ?: '/';
        $service = $this->app->comments();

        if (!$service->isEnabled()) {
            $this->app->redirect($referrer);
            return;
        }

        $post = $this->app->request()->data;
        $userId = function_exists('user_id') ? user_id() : null;

        if ($userId === null && !$service->allowsGuestComments()) {
            $this->app->redirect($referrer);
            return;
        }

        $body = trim((string) ($post->body ?? ''));

        if ($body === '') {
            $this->app->redirect($referrer);
            return;
        }

        $data = [
            'commentable_type' => $type,
            'commentable_id'   => (int) $id,
            'body'             => $body,
            'ip_address'       => $this->app->request()->ip,
        ];

        if (!empty($post->parent_id)) {
            $data['parent_id'] = (int) $post->parent_id;
        }

        $captchaField = $service->getCaptchaPostField();
        if ($captchaField !== '') {
            $data['captcha_token'] = (string) ($post->$captchaField ?? '');
        }

        if ($userId !== null) {
            $data['user_id'] = $userId;
        } else {
            $guestName = trim((string) ($post->guest_name ?? ''));
            if ($guestName === '') {
                $this->app->redirect($referrer);
                return;
            }
            $data['guest_name'] = $guestName;
            $data['guest_email'] = trim((string) ($post->guest_email ?? ''));
            $data['guest_website'] = trim((string) ($post->guest_website ?? ''));
        }

        try {
            $service->create($data);
        } catch (ValidationException $e) {
            $separator = str_contains($referrer, '?') ? '&' : '?';
            $this->app->redirect($referrer . $separator . 'comment_error=' . urlencode($e->getMessage()));
            return;
        }

        $this->app->redirect($referrer);
    }
}
