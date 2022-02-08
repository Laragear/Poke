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

    public function test_blade_mode_doesnt_inject_script(): void
    {
        $this->get('form')->assertDontSee('start-poke-script');
    }

    public function test_blade_mode_renders_once_multiple_declarations(): void
    {
        $this->get('component-multiple')
            ->assertSee('start-poke-script')
            ->assertSee(
                Blade::render('<body><x-laragear.poke-script /></body>'), false
            );
    }
}