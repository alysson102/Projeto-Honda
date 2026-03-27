<?php

declare(strict_types=1);

namespace App\Core;

final class App
{
    public function run(): void
    {
        SecurityHeaders::apply();

        $request = Request::capture();
        $response = new Response();

        $router = new Router($request, $response);
        $routes = require base_path('app/Config/routes.php');
        $routes($router);

        $router->dispatch();
    }
}
