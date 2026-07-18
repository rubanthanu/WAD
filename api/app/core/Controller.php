<?php

abstract class Controller
{
    
    protected PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    protected function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    protected function getInput(): array
    {
        $data = json_decode(file_get_contents('php://input'), true);
        return is_array($data) ? $data : [];
    }

    protected function getQueryParam(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    protected function getUserId(): int|string|null
    {
        return Session::getUserId();
    }

    protected function getUserRole(): ?string
    {
        return Session::getUserRole();
    }

    protected function requireAuth(): void
    {
        if (!Session::isAuthenticated()) {
            Response::error("Unauthorized access. Please login.", 401);
        }
    }

    protected function requireRole(string|array $roles, string $message = "Access denied."): void
    {
        $this->requireAuth();

        $allowed = is_array($roles) ? $roles : [$roles];
        if (!in_array($this->getUserRole(), $allowed, true)) {
            Response::error($message, 403);
        }
    }
}
