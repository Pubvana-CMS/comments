<?php

use Enlivenapp\FlightCsrf\Middlewares\CsrfMiddleware;
use Enlivenapp\FlightShield\Middlewares\SessionAuthMiddleware;
use Pubvana\Comments\Controllers\CommentsAdminController;

/** @var \flight\net\Router $router */
/** @var \flight\Engine $app */
/** @var string $configPrepend */

// Settings page
$router->get('/comments/settings', function () use ($app, $configPrepend) {
    (new CommentsAdminController($app, $configPrepend))->settings();
})->addMiddleware(new SessionAuthMiddleware($app));

// Save settings
$router->post('/comments/settings', function () use ($app, $configPrepend) {
    (new CommentsAdminController($app, $configPrepend))->saveSettings();
})->addMiddleware(new SessionAuthMiddleware($app))
  ->addMiddleware(new CsrfMiddleware($app));

// Moderation list
$router->get('/comments', function () use ($app, $configPrepend) {
    (new CommentsAdminController($app, $configPrepend))->index();
})->addMiddleware(new SessionAuthMiddleware($app));

// Single comment detail
$router->get('/comments/@id', function (string $id) use ($app, $configPrepend) {
    (new CommentsAdminController($app, $configPrepend))->show($id);
})->addMiddleware(new SessionAuthMiddleware($app));

// Approve
$router->post('/comments/@id/approve', function (string $id) use ($app, $configPrepend) {
    (new CommentsAdminController($app, $configPrepend))->approve($id);
})->addMiddleware(new SessionAuthMiddleware($app))
  ->addMiddleware(new CsrfMiddleware($app));

// Reject
$router->post('/comments/@id/reject', function (string $id) use ($app, $configPrepend) {
    (new CommentsAdminController($app, $configPrepend))->reject($id);
})->addMiddleware(new SessionAuthMiddleware($app))
  ->addMiddleware(new CsrfMiddleware($app));

// Delete
$router->post('/comments/@id/delete', function (string $id) use ($app, $configPrepend) {
    (new CommentsAdminController($app, $configPrepend))->delete($id);
})->addMiddleware(new SessionAuthMiddleware($app))
  ->addMiddleware(new CsrfMiddleware($app));
