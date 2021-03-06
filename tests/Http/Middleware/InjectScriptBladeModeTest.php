<?php

namespace Tests\Http\Middleware;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class InjectScriptBladeModeTest extends TestCase
{
    use DefinesWebRoutes;

    protected function defineEnvironment($app)
    {
        $app->make('config')->set('poke.mode', 'blade');
    }

    public function test_doesnt_inject_script(): void
    {
        $this->get('form')->assertDontSee('start-poke-script');
    }

    public function test_doesnt_inject_scripts_using_middleware(): void
    {
        $this->addMiddleware('form', 'poke');

        $this->get('form')->assertDontSee('start-poke-script');
    }

    public function test_doesnt_injects_on_redirect(): void
    {
        $this->get('redirect')->assertDontSee('start-poke-script');
    }

    public function test_renders_once_on_multiple_declarations(): void
    {
        $this->get('component-multiple')
            ->assertSee('start-poke-script')
            ->assertSee(
                Blade::render('<body><x-poke-script /></body>'), false
            );
    }

    public function test_component_rendered_forcefully_doesnt_render_twice(): void
    {
        $this->app->make('router')->get('component-multiple-forced', function () {
            return Blade::render(
                '<html><head></head><body><x-poke-script /><x-poke-script :force="true" /></body></html>'
            );
        });

        $this->get('component-multiple-forced')->assertSee(
            Blade::render('<html><head></head><body><x-poke-script /></body></html>'), false
        );
    }
}
