<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Minimalist router: maps GET/POST URLs to [ControllerClass, method] handlers.
 * Controllers must extend App\Core\Controller (or be callable).
 */
class Router
{
    private array $routes = [];

    public function get(string $uri, string $class, string $method): void
    {
        $this->routes['GET'][$uri] = [$class, $method];
    }

    public function post(string $uri, string $class, string $method): void
    {
        $this->routes['POST'][$uri] = [$class, $method];
    }

    public function dispatch(): void
    {
        $request = new Request();

        $uri    = $request->getPath();
        $method = $request->getMethod();

        if (isset($this->routes[$method][$uri])) {
            [$class, $action] = $this->routes[$method][$uri];
            $controller = new $class();
            $controller->$action();
        } else {
            http_response_code(404);
            echo "<h1>404 Not Found</h1>";
        }
    }
}
