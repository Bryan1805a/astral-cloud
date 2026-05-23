<?php

class Router {
    private array $routes = [];

    public function get(string $uri, array $handler): void {
        $this->routes['GET'][$uri] = $handler;
    }

    public function post(string $uri, array $handler): void {
        $this->routes['POST'][$uri] = $handler;
    }

    public function dispatch(string $uri, string $method): void {
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';
        $method = strtoupper($method);

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
