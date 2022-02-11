<?php

namespace Tests\Blade\Components;

use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
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

        $view->assertSee("await fetch('http://localhost/poke'", false);
        $view->assertSee("if (navigator.onLine && new Date() - poke_last >= 1800000 + 7200000)", false);
        $view->assertSee("setInterval(() => { poke_renew(); }, 1800000 )", false);
    }

    public function useRandomConfig($app): void
    {
        $app->make('config')->set('session.lifetime', 100);
        $app->make('config')->set('poke.times', 10);
        $app->make('config')->set('poke.poking.route', 'test');
    }

    /**
     * @define-env useRandomConfig
     */
    public function test_renders_script_from_component_with_custom_values(): void
    {
        $view = $this->blade('<x-poke-script />');

        $view->assertSee("await fetch('http://localhost/test'", false);
        $view->assertSee("if (navigator.onLine && new Date() - poke_last >= 600000 + 6000000)", false);
        $view->assertSee("setInterval(() => { poke_renew(); }, 600000 );", false);
    }
}
