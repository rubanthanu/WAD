<?php

class Validator
{
    
    public static function required(array $data, array $fields): bool
    {
        foreach ($fields as $field) {
            if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
                return false;
            }
        }
        return true;
    }

    public static function validStatus(string $status): bool
    {
        $allowed = ['pending', 'confirmed', 'cancelled', 'completed'];
        return in_array($status, $allowed, true);
    }
}
