<?php

class Response
{
    
    public static function json(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        echo json_encode($data);
        exit();
    }

    public static function success(string $message, array $extra = [], int $statusCode = 200): void
    {
        $payload = array_merge(["success" => true, "message" => $message], $extra);
        self::json($payload, $statusCode);
    }

    public static function error(string $message, int $statusCode = 400): void
    {
        self::json(["error" => $message], $statusCode);
    }
}
