<?php

declare(strict_types=1);

namespace Pubvana\Comments\Controllers;

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
     * Display comments for a content item (stub).
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
     * Handle comment submission (stub).
     */
    public function store(string $type, string $id): void
    {
        // @todo: implement public comment submission
        $this->app->render('comments/form', [
            'commentableType' => $type,
            'commentableId'  => (int) $id,
        ]);
    }
}
