<?php

namespace Tests;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Laragear\MetaTesting\InteractsWithServiceProvider;
use Laragear\Poke\Blade\Components\Script;
use Laragear\Poke\Http\Controllers\PokeController;
use Laragear\Poke\Http\Middleware\InjectScript;
use Laragear\Poke\PokeServiceProvider;

class PokeServiceProviderTest extends TestCase
{
    use InteractsWithServiceProvider;

    public function test_receives_default_config(): void
    {
        $this->assertConfigMerged(__DIR__.'/../config/poke.php', 'poke');
    }

    public function test_registers_view_injector_as_singleton(): void
    {
        $this->assertHasSingletons(InjectScript::class);
    }

    public function test_loads_default_poke_route()
    {
        $route = $this->assertRouteByName('poke');

        static::assertTrue($route->matches(Request::create('/poke', 'HEAD')));
        static::assertInstanceOf(PokeController::class, $route->getController());
    }

    public function test_registers_script_view(): void
    {
        $this->assertHasViews(PokeServiceProvider::VIEWS, 'poke');
    }

    public function test_registers_web_middleware_as_poke(): void
    {
        $this->assertHasMiddlewareAlias('poke', InjectScript::class);
        $this->assertHasMiddlewareInGroup('web', InjectScript::class);
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
        $this->assertPublishes($this->app->configPath('poke.php'), 'config');
    }

    public function test_publishes_view(): void
    {
        $this->assertPublishes($this->app->viewPath('vendor/poke'), 'views');
    }

    public function test_registers_blade_component(): void
    {
        $this->assertHasBladeComponent('poke-script', Script::class);
    }
}
