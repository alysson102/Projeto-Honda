<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    private string $method;
    private string $path;
    private array $query;
    private array $body;

    public function __construct(
        string $method,
        string $path,
        array $query,
        array $body
    ) {
        $this->method = $method;
        $this->path = $path;
        $this->query = $query;
        $this->body = $body;
    }

    public static function capture(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper((string) $_POST['_method']);
        }

        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $requestPath = (string) parse_url($uri, PHP_URL_PATH);
        $basePath = base_url_path();

        if ($basePath !== '' && str_starts_with($requestPath, $basePath)) {
            $requestPath = substr($requestPath, strlen($basePath));
        }

        $path = '/' . trim($requestPath, '/');
        $path = $path === '//' ? '/' : $path;

        $query = $_GET;
        $body = $_POST;

        $contentTypeHeader = (string) ($_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '');
        $contentType = mb_strtolower($contentTypeHeader);

        if (str_contains($contentType, 'application/json')) {
            $rawBody = file_get_contents('php://input');
            if (is_string($rawBody) && $rawBody !== '') {
                $decodedBody = json_decode($rawBody, true);
                if (is_array($decodedBody)) {
                    $body = array_merge($body, $decodedBody);
                }
            }
        }

        if (!is_array($query)) {
            $query = [];
        }

        if (!is_array($body)) {
            $body = [];
        }

        return new self($method, $path, $query, $body);
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }
}
