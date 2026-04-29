<?php

declare(strict_types=1);

namespace Pubvana\Comments;

use Enlivenapp\FlightSchool\PluginInterface;
use Pubvana\Comments\Services\CommentService;
use flight\Engine;
use flight\net\Router;
use Flight;

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
    }
}
