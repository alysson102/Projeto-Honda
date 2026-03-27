<?php

declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    public static function token(): string
    {
        $name = (string) Config::get('security.csrf_token_name', '_token');

        if (empty($_SESSION['_csrf'][$name])) {
            $_SESSION['_csrf'][$name] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf'][$name];
    }

    public static function field(): string
    {
        $name = (string) Config::get('security.csrf_token_name', '_token');
        return '<input type="hidden" name="' . e($name) . '" value="' . e(self::token()) . '">';
    }

    public static function validate(?string $token): bool
    {
        $name = (string) Config::get('security.csrf_token_name', '_token');
        $sessionToken = $_SESSION['_csrf'][$name] ?? '';

        return is_string($token) && hash_equals((string) $sessionToken, $token);
    }
}
