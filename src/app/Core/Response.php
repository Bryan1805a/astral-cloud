<?php

declare(strict_types=1);

namespace App\Core;

class Response
{
    private array $headers = [];

    public function json(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public function redirect(string $url, int $status = 302): never
    {
        http_response_code($status);
        header("Location: {$url}");
        exit;
    }

    public function setStatusCode(int $code): self
    {
        http_response_code($code);
        return $this;
    }

    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        header("{$name}: {$value}");
        return $this;
    }

    // ── CSRF ──────────────────────────────────────────────

    public function csrfToken(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    public function csrfField(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . $this->csrfToken() . '">';
    }

    public function verifyCsrf(Request $request): void
    {
        $token = $request->post('_csrf_token', '');
        if (empty($token) || !hash_equals($_SESSION['_csrf_token'] ?? '', $token)) {
            $this->setStatusCode(419);
            die("<h2 style='color:red;text-align:center;margin-top:50px;'>419 - CSRF token validation failed. Please go back and try again.</h2>");
        }
    }

    // ── View rendering ────────────────────────────────────

    public function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../../Views/layouts/header.php';
        require __DIR__ . "/../../Views/{$view}.php";
        require __DIR__ . '/../../Views/layouts/footer.php';
    }

    public function error(int $code, string $message = ''): never
    {
        $this->setStatusCode($code);
        die("<h1 style='text-align:center;margin-top:80px;color:#ef4444;'>{$code}</h1><p style='text-align:center;color:#94a3b8;'>{$message}</p>");
    }
}
