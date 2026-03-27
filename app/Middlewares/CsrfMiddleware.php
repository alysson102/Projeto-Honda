<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Config;
use App\Core\Csrf;
use App\Core\Request;

final class CsrfMiddleware
{
    public function handle(Request $request): void
    {
        $tokenName = (string) Config::get('security.csrf_token_name', '_token');
        $token = (string) $request->input($tokenName, '');

        if (Csrf::validate($token)) {
            return;
        }

        http_response_code(419);
        echo 'CSRF token inválido.';
        exit;
    }
}
