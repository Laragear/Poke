<?php

namespace Tests\Http;

use Laragear\Poke\Http\Controllers\PokeController;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use Tests\TestCase;

class RouteGeneratorTest extends TestCase
{
    protected function usesNoDomain($app): void
    {
        $app->make('config')->set('poke.poking', [
            'route' => 'test-route',
            'name' => 'test-name',
            'domain' => null,
            'middleware' => 'test-middleware',
        ]);
    }

    #[DefineEnvironment('usesNoDomain')]
    public function test_sets_global_route(): void
    {
        $route = $this->app->make('router')->getRoutes()->getByName('test-name');

        static::assertSame('test-name', $route->getName());
        static::assertSame(['HEAD'], $route->methods());
        static::assertSame(PokeController::class.'@__invoke', $route->getAction()['uses']);
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

    #[DefineEnvironment('usesSingleDomain')]
    public function test_set_one_domain_route(): void
    {
        $route = $this->app->make('router')->getRoutes()->getByName('test-name');

        static::assertSame('test-name', $route->getName());
        static::assertSame(['HEAD'], $route->methods());
        static::assertSame(PokeController::class.'@__invoke', $route->getAction()['uses']);
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

    #[DefineEnvironment('usesNoRoute')]
    public function test_doesnt_register_route(): void
    {
        static::assertNull($this->app->make('router')->getRoutes()->getByName('test-name'));
    }
}
