<?php

declare(strict_types=1);

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        $base = dirname(__DIR__);
        return $path === '' ? $base : $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('base_url_path')) {
    function base_url_path(): string
    {
        $appUrl = (string) config('app.url', '');
        $path = (string) parse_url($appUrl, PHP_URL_PATH);
        $path = '/' . trim($path, '/');

        // Accept APP_URL configured with or without /public.
        if ($path !== '/' && preg_match('#/public/?$#i', $path) === 1) {
            $path = preg_replace('#/public/?$#i', '', $path) ?? $path;
            $path = $path === '' ? '/' : $path;
        }

        return $path === '/' ? '' : $path;
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $cleanPath = '/' . ltrim($path, '/');
        $base = base_url_path();

        if ($cleanPath === '/') {
            return $base === '' ? '/' : $base . '/';
        }

        return ($base === '' ? '' : $base) . $cleanPath;
    }
}

if (!function_exists('e')) {
    function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('old')) {
    function old(string $key, string $default = ''): string
    {
        $old = $_SESSION['_old'] ?? [];
        return e((string)($old[$key] ?? $default));
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        return App\Core\Config::get($key, $default);
    }
}
