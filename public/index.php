<?php

declare(strict_types=1);

use App\Http\JsonResponse;
use App\Http\Router;

require __DIR__ . '/../vendor/autoload.php';

$router = new Router();

$router->add('GET', '/health', fn () => JsonResponse::send(['data' => ['status' => 'ok']]));

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);