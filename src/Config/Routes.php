<?php

use Pubvana\Comments\Controllers\CommentsController;

/** @var \flight\net\Router $router */
/** @var \flight\Engine $app */
/** @var string $configPrepend */

// Public comment display (stub)
$router->get('/@type/@id', function (string $type, string $id) use ($app, $configPrepend) {
    (new CommentsController($app, $configPrepend))->index($type, $id);
});

// Public comment submission (stub)
$router->post('/@type/@id', function (string $type, string $id) use ($app, $configPrepend) {
    (new CommentsController($app, $configPrepend))->store($type, $id);
});
