<?php

namespace Tests;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Laragear\Poke\Blade\Components\Script;
use Laragear\Poke\Http\Controllers\PokeController;
use Laragear\Poke\Http\Middleware\InjectScript;
use Laragear\Poke\PokeServiceProvider;

class PokeServiceProviderTest extends TestCase
{
    public function test_receives_default_config(): void
    {
        static::assertEquals(
            File::getRequire(__DIR__.'/../config/poke.php'),
            $this->app->make('config')->get('poke')
        );
    }

    public function test_loads_default_poke_route()
    {
        /** @var \Illuminate\Routing\Router $router */
        $router = $this->app->make('router');

        $route = $router->getRoutes()->match(
            $this->app->make('request')->create('/poke', 'HEAD')
        );

        static::assertEquals('poke', $route->getName());
        static::assertInstanceOf(PokeController::class, $route->getController());
    }

    public function test_registers_script_view(): void
    {
        static::assertTrue($this->app->make('view')->exists('poke::script'));
    }

    public function test_registers_web_middleware_as_poke(): void
    {
        static::assertContains(InjectScript::class, $this->app->make(Kernel::class)->getMiddlewareGroups()['web']);

        static::assertArrayHasKey('poke', $this->app->make('router')->getMiddleware());
    }

    protected function setModeToNonAuto($app): void
    {
        $app->make('config')->set('poke.mode', 'non-auto');
    }

    /**
     * @define-env setModeToNonAuto
     */
    public function test_doesnt_registers_web_middleware_if_mode_not_auto(): void
    {
        static::assertNotContains(InjectScript::class, $this->app->make(Kernel::class)->getMiddlewareGroups()['web']);
    }

    public function test_publishes_config(): void
    {
        static::assertSame([
            PokeServiceProvider::CONFIG => $this->app->configPath('poke.php'),
        ], ServiceProvider::pathsToPublish(PokeServiceProvider::class, 'config'));
    }


    public function test_publishes_view(): void
    {
        static::assertSame([
            PokeServiceProvider::VIEWS => $this->app->viewPath('vendor/poke'),
        ], ServiceProvider::pathsToPublish(PokeServiceProvider::class, 'views'));
    }

    public function test_registers_blade_component(): void
    {
        $aliases = $this->app->make('blade.compiler')->getClassComponentAliases();

        static::assertArrayHasKey('poke-script', $aliases);
        static::assertSame(Script::class, $aliases['poke-script']);
    }
}
