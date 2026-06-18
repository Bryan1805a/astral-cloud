<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected Request $request;
    protected Response $response;

    public function __construct()
    {
        $this->request  = new Request();
        $this->response = new Response();
    }

    protected function render(string $view, array $data = []): void
    {
        $this->response->render($view, $data);
    }

    protected function redirect(string $url): never
    {
        $this->response->redirect($url);
    }

    protected function json(array $data, int $status = 200): never
    {
        $this->response->json($data, $status);
    }

    protected function csrfField(): string
    {
        return $this->response->csrfField();
    }

    protected function verifyCsrf(): void
    {
        $this->response->verifyCsrf($this->request);
    }

    protected function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    protected function requireLogin(): void
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('/login');
        }
    }

    protected function requireAdmin(): void
    {
        $this->requireLogin();
        $role = $this->request->session['user_role'] ?? '';
        if ($role !== 'admin' && $role !== 'staff') {
            $this->response->error(403, 'Access denied.');
        }
    }

    protected function user(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    protected function userId(): ?int
    {
        return isset($_SESSION['user_id'])
            ? (int) $_SESSION['user_id'] : null;
    }
}
