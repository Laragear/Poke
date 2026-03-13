<?php

namespace Tests\Blade\Components;

use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use Tests\TestCase;

class ScriptTest extends TestCase
{
    use InteractsWithViews;

    protected function defineEnvironment($app)
    {
        $app->make('config')->set('poke.mode', 'blade');
    }

    public function test_renders_empty_when_mode_not_blade(): void
    {
        $this->app->make('config')->set('poke.mode', 'not-blade');

        $this->blade('<x-poke-script />')->assertDontSeeText('start-poke-script');
    }

    public function test_renders_script_when_forced_and_mode_not_blade(): void
    {
        $this->app->make('config')->set('poke.mode', 'not-blade');

        $this->blade('<x-poke-script :force="true" />')->assertSeeText('start-poke-script');
    }

    public function test_renders_script_from_component_with_default_values(): void
    {
        $view = $this->blade('<x-poke-script />');

        $view->assertSee("route: 'http://localhost/poke'", false);
        $view->assertSee("retries: 4", false);
        $view->assertSee("interval: 1800000", false);
        $view->assertSee("lifetime: 7200000", false);
    }

    public function useRandomConfig($app): void
    {
        $app->make('config')->set('session.lifetime', 100);
        $app->make('config')->set('poke.times', 10);
        $app->make('config')->set('poke.poking.route', 'test');
    }

    #[DefineEnvironment('useRandomConfig')]
    public function test_renders_script_from_component_with_custom_values(): void
    {
        $view = $this->blade('<x-poke-script />');

        $view->assertSee("route: 'http://localhost/test'", false);
        $view->assertSee("retries: 10", false);
        $view->assertSee("interval: 600000", false);
        $view->assertSee("lifetime: 6000000", false);
    }
}
