<?php
require_once 'RouterBase.php';

use App\Controllers\BetController;
use App\Controllers\AuthController;

$router = new RouterBase();

$router->add('POST', '/bet', function() {
    $controller = new BetController();
    $controller->bet();
});

$router->add('GET', '/', function() {
    $controller = new BetController();
    $controller->index();
});

$router->add('POST', '/auth', function() {
    $controller = new AuthController();
    $controller->auth();
});

// Dispatch request
$router->dispatch();