<?php

declare(strict_types=1);

return [
    'app' => [
        'name' => $_ENV['APP_NAME'] ?? 'Projeto Honda',
        'env' => $_ENV['APP_ENV'] ?? 'production',
        'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL),
        'url' => $_ENV['APP_URL'] ?? 'http://localhost/Projeto-Honda',
    ],
    'security' => [
        'session_name' => $_ENV['SESSION_NAME'] ?? 'proj_honda_session',
        'csrf_token_name' => '_token',
        'password_algo' => defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT,
        'login_max_attempts' => (int) ($_ENV['LOGIN_MAX_ATTEMPTS'] ?? 5),
        'login_attempt_window_seconds' => (int) ($_ENV['LOGIN_ATTEMPT_WINDOW_SECONDS'] ?? 900),
        'login_lockout_seconds' => (int) ($_ENV['LOGIN_LOCKOUT_SECONDS'] ?? 900),
        'agendamento_submit_max_attempts' => (int) ($_ENV['AGENDAMENTO_SUBMIT_MAX_ATTEMPTS'] ?? 10),
        'agendamento_submit_window_seconds' => (int) ($_ENV['AGENDAMENTO_SUBMIT_WINDOW_SECONDS'] ?? 300),
        'agendamento_submit_block_seconds' => (int) ($_ENV['AGENDAMENTO_SUBMIT_BLOCK_SECONDS'] ?? 600),
        'agendamento_api_max_attempts' => (int) ($_ENV['AGENDAMENTO_API_MAX_ATTEMPTS'] ?? 80),
        'agendamento_api_window_seconds' => (int) ($_ENV['AGENDAMENTO_API_WINDOW_SECONDS'] ?? 60),
        'agendamento_api_block_seconds' => (int) ($_ENV['AGENDAMENTO_API_BLOCK_SECONDS'] ?? 120),
        'force_https' => filter_var($_ENV['FORCE_HTTPS'] ?? false, FILTER_VALIDATE_BOOL),
        'hsts_enabled' => filter_var($_ENV['HSTS_ENABLED'] ?? false, FILTER_VALIDATE_BOOL),
        'hsts_max_age' => (int) ($_ENV['HSTS_MAX_AGE'] ?? 31536000),
    ],
    'database' => [
        'driver' => $_ENV['DB_DRIVER'] ?? 'mysql',
        'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
        'port' => $_ENV['DB_PORT'] ?? '3306',
        'database' => $_ENV['DB_DATABASE'] ?? 'projeto_honda',
        'username' => $_ENV['DB_USERNAME'] ?? 'root',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'charset' => 'utf8mb4',
    ],
];
