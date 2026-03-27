<?php

declare(strict_types=1);

namespace App\Core;

final class SecurityHeaders
{
    public static function apply(): void
    {
        if ((bool) config('security.force_https', false) === true && !self::isSecureRequest()) {
            $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
            $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
            header('Location: https://' . $host . $uri, true, 302);
            exit;
        }

        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('X-XSS-Protection: 0');
        header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'self'; base-uri 'self'; form-action 'self'");

        if ((bool) config('security.hsts_enabled', false) === true && self::isSecureRequest()) {
            $maxAge = (int) config('security.hsts_max_age', 31536000);
            header('Strict-Transport-Security: max-age=' . $maxAge . '; includeSubDomains; preload');
        }
    }

    private static function isSecureRequest(): bool
    {
        if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
            return true;
        }

        $forwardedProto = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));

        return $forwardedProto === 'https';
    }
}
