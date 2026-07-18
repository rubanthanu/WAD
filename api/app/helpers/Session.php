<?php

class Session
{
    
    public static function getUserId(): int|string|null
    {
        return $_SESSION['user_id'] ?? $_SERVER['HTTP_X_USER_ID'] ?? null;
    }

    public static function getUserRole(): ?string
    {
        return $_SESSION['role'] ?? $_SERVER['HTTP_X_USER_ROLE'] ?? null;
    }

    public static function isAuthenticated(): bool
    {
        return self::getUserId() !== null;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function destroy(): void
    {
        session_unset();
        session_destroy();
    }
}
