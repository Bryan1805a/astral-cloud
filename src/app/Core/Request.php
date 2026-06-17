<?php

declare(strict_types=1);

namespace App\Core;

class Request
{
    public readonly string $method;
    public readonly string $uri;
    public readonly string $path;
    public readonly array $query;
    public readonly array $body;
    public readonly array $server;
    public readonly array $session;
    public readonly ?string $clientIp;
    public readonly bool $isAjax;

    public function __construct()
    {
        $this->method   = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $this->uri      = $_SERVER['REQUEST_URI'] ?? '/';
        $this->path     = rtrim(parse_url($this->uri, PHP_URL_PATH) ?: '/', '/') ?: '/';
        $this->query    = $_GET;
        $this->body     = $_POST;
        $this->server   = $_SERVER;
        $this->isAjax   = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        $this->clientIp = $this->resolveClientIp();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getClientIp(): string
    {
        return $this->clientIp ?? '127.0.0.1';
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function isAjaxRequest(): bool
    {
        return $this->isAjax;
    }

    private function resolveClientIp(): string
    {
        foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP'] as $header) {
            $value = $_SERVER[$header] ?? '';
            if ($value) {
                $ip = trim(explode(',', $value)[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}
