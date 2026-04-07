<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\ProfileController;
use App\Core\Router;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\CsrfMiddleware;

return static function (Router $router): void {
    $router->get('/', [HomeController::class, 'index']);
    $router->get('/agendamento', [HomeController::class, 'agendamento'], [AuthMiddleware::class]);
    $router->post('/agendamento', [\App\Controllers\AgendamentoController::class, 'store'], [AuthMiddleware::class, CsrfMiddleware::class]);
    $router->post('/api/verificar-disponibilidade', [\App\Controllers\AgendamentoController::class, 'verificarDisponibilidade'], [AuthMiddleware::class, CsrfMiddleware::class]);
    $router->get('/about', [HomeController::class, 'about']);
    $router->get('/pecas', [HomeController::class, 'pecas']);
    $router->get('/info-revisoes', [HomeController::class, 'infoRevisoes']);
    $router->get('/contact', [HomeController::class, 'contact']);
    $router->get('/register', [HomeController::class, 'register']);
    $router->post('/register', [AuthController::class, 'register'], [CsrfMiddleware::class]);

    $router->get('/login', [AuthController::class, 'showLogin']);
    $router->post('/login', [AuthController::class, 'login'], [CsrfMiddleware::class]);
    $router->post('/logout', [AuthController::class, 'logout'], [AuthMiddleware::class, CsrfMiddleware::class]);

    $router->get('/perfil', [ProfileController::class, 'index'], [AuthMiddleware::class]);
    $router->post('/perfil/atualizar', [ProfileController::class, 'update'], [AuthMiddleware::class, CsrfMiddleware::class]);
    $router->post('/perfil/foto', [ProfileController::class, 'updatePhoto'], [AuthMiddleware::class, CsrfMiddleware::class]);
    $router->post('/perfil/foto/excluir', [ProfileController::class, 'deletePhoto'], [AuthMiddleware::class, CsrfMiddleware::class]);
    $router->post('/perfil/agendamentos/excluir', [ProfileController::class, 'deleteAppointment'], [AuthMiddleware::class, CsrfMiddleware::class]);
};
