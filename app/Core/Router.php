<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

final class Router
{
    private array $routes = [];
    private Request $request;
    private Response $response;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function get(string $path, array $handler, array $middlewares = []): void
    {
        $this->add('GET', $path, $handler, $middlewares);
    }

    public function post(string $path, array $handler, array $middlewares = []): void
    {
        $this->add('POST', $path, $handler, $middlewares);
    }

    public function add(string $method, string $path, array $handler, array $middlewares = []): void
    {
        $this->routes[$method][] = [
            'path' => $path,
            'handler' => $handler,
            'middlewares' => $middlewares,
        ];
    }

    public function dispatch(): void
    {
        try {
            $methodRoutes = $this->routes[$this->request->method()] ?? [];

            foreach ($methodRoutes as $route) {
                if ($route['path'] !== $this->request->path()) {
                    continue;
                }

                foreach ($route['middlewares'] as $middlewareClass) {
                    $middleware = new $middlewareClass();
                    $middleware->handle($this->request);
                }

                [$controllerClass, $action] = $route['handler'];
                $controller = new $controllerClass($this->request, $this->response);
                $controller->$action();
                return;
            }

            $this->response->status(404);
            View::render('errors/404', ['title' => 'Página não encontrada']);
        } catch (Throwable $exception) {
            if ((bool) Config::get('app.debug', false) === true) {
                throw $exception;
            }

            $this->response->status(500);
            View::render('errors/500', ['title' => 'Erro interno']);
        }
    }
}
