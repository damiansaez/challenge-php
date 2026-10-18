<?php

declare(strict_types=1);

namespace App\Http;

final class Router
{
    /** @var array<int, array{method: string, pattern: string, handler: callable}> */
    private array $routes = [];

    public function add(string $method, string $path, callable $handler): void
    {
        $pattern = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $path) . '$#';

        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = rtrim((string) parse_url($uri, PHP_URL_PATH), '/') ?: '/';

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                ($route['handler'])($params);
                return;
            }
        }

        JsonResponse::send(['error' => ['code' => 404, 'message' => 'Ruta no encontrada']], 404);
    }
}