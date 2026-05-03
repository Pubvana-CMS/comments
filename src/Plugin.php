<?php

declare(strict_types=1);

namespace Pubvana\Comments;

use Enlivenapp\FlightSchool\PluginInterface;
use Pubvana\Comments\Services\CommentService;
use flight\Engine;
use flight\net\Router;
use Flight;

/**
 * Comments plugin — registers the comment service, admin menus, and content blocks.
 */
class Plugin implements PluginInterface
{
    public function register(Engine $app, Router $router, array $config = []): void
    {
        // Map the comments service as a singleton
        $app->map('comments', function () {
            static $instance = null;
            if ($instance === null) {
                $instance = new CommentService(Flight::db());
            }
            return $instance;
        });

        // Register admin menu with submenu
        $app->adext('menu', 'content', 'pubvana.comments', [
            'label'    => 'Comments',
            'icon'     => 'ti-message-circle',
            'priority' => 30,
            'submenu'  => [
                'manage' => [
                    'label'    => 'Manage',
                    'url'      => '/comments',
                    'priority' => 10,
                ],
                'settings' => [
                    'label'    => 'Settings',
                    'url'      => '/comments/settings',
                    'priority' => 20,
                ],
            ],
        ]);

        $app->adext('page', 'dashboard.cards', 'pubvana.comments', [
            'label'    => 'Comments',
            'priority' => 30,
            'callable' => function (array $context) use ($app): array {
                $pending = $app->comments()->countByStatus('pending');

                return [[
                    'id'          => 'pending-comments',
                    'label'       => 'Pending Comments',
                    'value'       => $pending,
                    'icon'        => 'ti-message-circle',
                    'tone'        => $pending > 0 ? 'warning' : 'secondary',
                    'href'        => '/comments?status=pending',
                    'description' => $pending > 0
                        ? 'Comments waiting for moderation.'
                        : 'No comments are waiting for review.',
                ]];
            },
        ]);

        $app->adext('page', 'dashboard.sections', 'pubvana.comments', [
            'label'    => 'Comments',
            'priority' => 10,
            'callable' => function (array $context) use ($app): array {
                $pending = $app->comments()->list(1, 5, 'pending');
                $items = [];

                foreach ($pending as $comment) {
                    $author = $comment->guest_name ?: ($comment->user_id ? 'User #' . $comment->user_id : 'Anonymous');
                    $items[] = [
                        'label'    => $author . ' on ' . $comment->commentable_type . ' #' . $comment->commentable_id,
                        'meta'     => date('M j, Y g:ia', strtotime((string) $comment->created_at)),
                        'href'     => '/comments/' . (int) $comment->id,
                        'emphasis' => 'warning',
                    ];
                }

                return [[
                    'id'          => 'comments-awaiting-review',
                    'title'       => 'Comments Awaiting Review',
                    'type'        => 'list',
                    'icon'        => 'ti-message-2-exclamation',
                    'tone'        => 'warning',
                    'href'        => '/comments?status=pending',
                    'empty_state' => 'No comments are waiting for review.',
                    'items'       => $items,
                ]];
            },
        ]);

        $app->adext('block', 'available', 'pubvana.comments.recent', [
            'label'       => 'Recent Comments',
            'description' => 'Latest approved comments across the site',
            'provider'    => function (array $options) use ($app) {
                $count = (int) ($options['count'] ?? 5);
                $rows = $app->comments()->list(1, $count, 'approved');
                $comments = [];
                foreach ($rows as $comment) {
                    $authorName = $comment->guest_name ?: 'Anonymous';
                    if ($comment->user_id !== null) {
                        $user = (new \Enlivenapp\FlightShield\Models\User(Flight::db()))
                            ->findById((int) $comment->user_id);
                        $authorName = $user->username ?? 'Unknown';
                    }

                    $postTitle = '';
                    $postUrl = '#';
                    if ($comment->commentable_type === 'blog') {
                        $post = $app->blog()->findPost((int) $comment->commentable_id);
                        if ($post) {
                            $postTitle = $post->title;
                            $postUrl = $app->pluginLoader()->routePrefix('pubvana/blog') . '/' . $post->slug;
                        }
                    }

                    $comments[] = [
                        'author'     => $authorName,
                        'post_title' => $postTitle,
                        'url'        => $postUrl,
                        'date'       => $comment->created_at,
                    ];
                }
                return [
                    'title'    => $options['title'] ?? 'Recent Comments',
                    'comments' => $comments,
                ];
            },
            'template'    => 'pubvana/comments/public/blocks/recent-comments',
            'priority'    => 10,
            'options'     => [
                'title' => ['type' => 'input', 'label' => 'Title', 'default' => 'Recent Comments'],
                'count' => ['type' => 'input', 'label' => 'Number of comments', 'default' => '5'],
            ],
        ]);

    }
}
