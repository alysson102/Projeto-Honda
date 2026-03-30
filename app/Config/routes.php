<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Core\Router;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\CsrfMiddleware;

return static function (Router $router): void {
    $router->get('/', [HomeController::class, 'index']);
    $router->get('/agendamento', [HomeController::class, 'agendamento']);
    $router->post('/agendamento', [\App\Controllers\AgendamentoController::class, 'store'], [CsrfMiddleware::class]);
    $router->post('/api/verificar-disponibilidade', [\App\Controllers\AgendamentoController::class, 'verificarDisponibilidade'], [CsrfMiddleware::class]);
    $router->get('/about', [HomeController::class, 'about']);
    $router->get('/contact', [HomeController::class, 'contact']);
    $router->get('/register', [HomeController::class, 'register']);
    $router->post('/register', [AuthController::class, 'register'], [CsrfMiddleware::class]);

    $router->get('/login', [AuthController::class, 'showLogin']);
    $router->post('/login', [AuthController::class, 'login'], [CsrfMiddleware::class]);
    $router->post('/logout', [AuthController::class, 'logout'], [AuthMiddleware::class, CsrfMiddleware::class]);
};
