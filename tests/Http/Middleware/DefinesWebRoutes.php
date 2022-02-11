<?php

namespace Tests\Http\Middleware;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Blade;
use function response;

trait DefinesWebRoutes
{
    protected function defineWebRoutes($router)
    {
        $router->get('form', function () {
            return Blade::render(
                '<html><head></head><body>@csrf</body></html>'
            );
        });

        $router->get('form-multiple', function () {
            return Blade::render(
                '<html><head></head><body>@csrf @csrf</body></html>'
            );
        });

        $router->get('component', function () {
            return Blade::render(
                '<html><head></head><body><x-poke-script /></body></html>'
            );
        });

        $router->get('form-with-component', function () {
            return Blade::render(
                '<html><head></head><body>@csrf<x-poke-script /></body></html>'
            );
        });

        $router->get('component-multiple', function () {
            return Blade::render(
                '<html><head></head><body><x-poke-script /><x-poke-script /></body></html>'
            );
        });

        $router->get('no-form', function () {
            return Blade::render(
                '<html><head></head><body></body></html>'
            );
        });

        $router->get('no-end-body', function () {
            return Blade::render(
                '<html><head></head><body>@csrf</html>'
            );
        });

        $router->get('error', function () {
            response(Blade::render(
                '<html><head></head><body>@csrf</body></html>'
            ), 500)->throwResponse();
        });
    }

    public function addMiddleware(string $path, string $middleware): Route
    {
        return $this->app->make('router')->getRoutes()->getRoutesByMethod()['GET'][$path]->middleware($middleware);
    }
}
