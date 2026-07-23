<?php

class Validator
{
    /**
     * Checks if required fields exist and are non-empty in the input array.
     */
    public static function required(array $data, array $fields): bool
    {
        foreach ($fields as $field) {
            if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
                return false;
            }
        }
        return true;
    }

    /**
     * Validates email address format using filter_var.
     */
    public static function email(string $email): bool
    {
        return filter_var(trim($email), FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validates appointment status value against allowed values.
     */
    public static function validStatus(string $status): bool
    {
        $allowed = ['pending', 'confirmed', 'cancelled', 'completed'];
        return in_array($status, $allowed, true);
    }

    /**
     * Sanitizes string input.
     */
    public static function sanitize(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}
