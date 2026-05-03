<?php

declare(strict_types=1);

namespace Pubvana\Comments\Controllers;

use Pubvana\Admin\Controllers\AdminController;

/**
 * Admin controller for comment moderation — listing, approval, and deletion.
 */
class CommentsAdminController extends AdminController
{
    /**
     * Moderation list, filterable by status, paginated.
     */
    public function index(): void
    {
        $request  = $this->app->request();
        $status   = $request->query->status ?? null;
        $page     = (int) ($request->query->page ?? 1);
        $perPage  = 25;

        $service  = $this->app->comments();
        $list     = $service->list($page, $perPage, $status);

        $counts = [
            'all'      => $service->countByStatus(),
            'pending'  => $service->countByStatus('pending'),
            'approved' => $service->countByStatus('approved'),
            'rejected' => $service->countByStatus('rejected'),
        ];

        $totalForStatus = $status ? $counts[$status] ?? 0 : $counts['all'];
        $totalPages     = (int) ceil($totalForStatus / $perPage);

        $this->render('admin/index', [
            'pageTitle'  => 'Comments',
            'comments'   => $list,
            'counts'     => $counts,
            'status'     => $status,
            'page'       => $page,
            'totalPages' => $totalPages,
        ]);
    }

    /**
     * Single comment detail with moderation actions.
     */
    public function show(string $id): void
    {
        $comment = $this->app->comments()->find((int) $id);

        if ($comment === null) {
            $this->app->redirect('/admin/comments');
            return;
        }

        $this->render('admin/show', [
            'pageTitle' => 'Comment #' . $id,
            'comment'   => $comment,
        ]);
    }

    /**
     * Approve a comment.
     */
    public function approve(string $id): void
    {
        $this->app->comments()->approve((int) $id);
        $this->app->redirect('/admin/comments');
    }

    /**
     * Reject a comment.
     */
    public function reject(string $id): void
    {
        $this->app->comments()->reject((int) $id);
        $this->app->redirect('/admin/comments');
    }

    /**
     * Delete a comment.
     */
    public function delete(string $id): void
    {
        $this->app->comments()->delete((int) $id);
        $this->app->redirect('/admin/comments');
    }

    /**
     * Comment settings page.
     */
    public function settings(): void
    {
        $settings = $this->app->settings()->getClass('Comments', 'self');

        $this->render('admin/settings', [
            'pageTitle' => 'Comment Settings',
            'settings'  => $settings,
        ]);
    }

    /**
     * Save comment settings.
     */
    public function saveSettings(): void
    {
        $post = $this->app->request()->data->getData();
        unset($post['_csrf_token']);

        // Checkboxes won't POST when unchecked, so default booleans to false
        $post['comments_enabled']     = !empty($post['comments_enabled']);
        $post['allow_guest_comments'] = !empty($post['allow_guest_comments']);

        $this->app->settings()->saveClass('Comments', $post, 'self');

        $this->app->redirect('/admin/comments/settings');
    }
}
