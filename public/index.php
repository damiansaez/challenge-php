<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\ProductController;
use App\Http\ExceptionHandler;
use App\Http\JsonResponse;
use App\Http\Request;
use App\Http\Router;
use App\Repositories\ProductRepository;
use App\Services\PriceConverter;
use App\Services\ProductService;

require __DIR__ . '/../vendor/autoload.php';

ExceptionHandler::register();

$request = Request::fromGlobals();

// Composition root: se arma solo si la ruta lo necesita
$products = static fn (): ProductController => new ProductController(
    new ProductService(
        new ProductRepository(Database::getInstance()),
        PriceConverter::fromEnvironment(),
    ),
    $request,
);

$router = new Router();

$router->add('GET', '/health', fn () => JsonResponse::send(['data' => ['status' => 'ok']]));

$router->add('GET', '/productos', fn () => $products()->getAll());
$router->add('GET', '/productos/{id}', fn (string $id) => $products()->getById($id));
$router->add('POST', '/productos', fn () => $products()->create());
$router->add('PUT', '/productos/{id}', fn (string $id) => $products()->update($id));
$router->add('DELETE', '/productos/{id}', fn (string $id) => $products()->delete($id));

$router->dispatch($request->method, $request->uri);