<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    public function status(int $code): self
    {
        http_response_code($code);
        return $this;
    }

    public function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    public function redirect(string $path): void
    {
        $target = str_starts_with($path, 'http://') || str_starts_with($path, 'https://')
            ? $path
            : url($path);

        header('Location: ' . $target);
        exit;
    }
}
