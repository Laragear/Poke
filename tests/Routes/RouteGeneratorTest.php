<?php

namespace Tests\Routes;

use Illuminate\Routing\Router;
use Laragear\Poke\Http\Controllers\PokeController;
use Tests\TestCase;

class RouteGeneratorTest extends TestCase
{
    protected Router $router;

    protected function setUp() : void
    {
        parent::setUp();

        $this->router = $this->app->make('router');
    }

    protected function usesNoDomain($app): void
    {
        $app->make('config')->set('poke.poking', [
            'route' => 'test-route',
            'name' => 'test-name',
            'domain' => null,
            'middleware' => 'test-middleware',
        ]);
    }

    /**
     * @define-env usesNoDomain
     */
    public function test_sets_global_route(): void
    {
        $routes = $this->router->getRoutes()->getRoutes();

        static::assertCount(1, $routes);

        $route = $routes[0];

        static::assertSame('test-name', $route->getName());
        static::assertSame(['HEAD'], $route->methods());
        static::assertSame(PokeController::class . '@__invoke', $route->getAction()['uses']);
        static::assertSame(['test-middleware'], $route->middleware());

        static::assertNull($route->getDomain());
    }

    protected function usesSingleDomain($app): void
    {
        $app->make('config')->set('poke.poking', [
            'route' => 'test-route',
            'name' => 'test-name',
            'domain' => 'one',
            'middleware' => 'test-middleware',
        ]);
    }

    /**
     * @define-env usesSingleDomain
     */
    public function testSetOneDomainRoute()
    {
        $route = $this->router->getRoutes()->getRoutes()[0];

        static::assertSame('test-name', $route->getName());
        static::assertSame(['HEAD'], $route->methods());
        static::assertSame(PokeController::class . '@__invoke', $route->getAction()['uses']);
        static::assertSame(['test-middleware'], $route->middleware());

        static::assertSame('one', $route->getDomain());
    }

    protected function usesNoRoute($app): void
    {
        $app->make('config')->set('poke.poking', [
            'route' => false,
            'name' => 'test-name',
            'domain' => ['one', 'two', 'three'],
            'middleware' => 'test-middleware',
        ]);
    }

    /**
     * @define-env usesNoRoute
     */
    public function testDoesntRegisterRoute()
    {
        static::assertEmpty($this->router->getRoutes()->getRoutes());
    }
}
