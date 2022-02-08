<?php

namespace Tests\Http\Middleware;

use Tests\TestCase;
use function substr_count;

class InjectScriptMiddlewareModeTest extends TestCase
{
    use DefinesWebRoutes;

    protected function defineEnvironment($app)
    {
        $app->make('config')->set('poke.mode', 'middleware');
    }

    public function test_doesnt_injects_when_not_declared(): void
    {
        $this->get('form')->assertDontSee('start-poke-script');
    }

    public function test_injects_script_with_csrf_input_present(): void
    {
        $this->addMiddleware('form', 'poke');

        $this->get('form')->assertSee('start-poke-script');
    }

    public function test_injects_only_once_with_not_forced_component(): void
    {
        $this->addMiddleware('form-with-component', 'poke');

        static::assertSame(1, substr_count($this->get('form-with-component')->getContent(), 'start-poke-script'));
    }

    public function test_injects_script_before_closing_body_tag(): void
    {
        $this->addMiddleware('form', 'poke');

        $this->get('form')->assertSee(<<<BODYEND
        // end-poke-script
    </script>
</body>
BODYEND
            , false);
    }

    public function test_doesnt_inject_script_without_csrf_input(): void
    {
        $this->addMiddleware('no-form', 'poke');

        $this->get('no-form')->assertDontSee('start-poke-script');
    }

    public function test_doesnt_inject_script_without_closing_body_tag(): void
    {
        $this->addMiddleware('no-end-body', 'poke');

        $this->get('no-end-body')->assertDontSee('start-poke-script');
    }

    public function test_doesnt_injects_on_not_successful(): void
    {
        $this->addMiddleware('error', 'poke');

        $this->get('error')->assertDontSee('start-poke-script');
    }

    public function test_doesnt_injects_on_json(): void
    {
        $this->addMiddleware('form', 'poke');

        $this->getJson('form')->assertDontSee('start-poke-script');
    }

    public function test_forced_injection_on_form(): void
    {
        $this->addMiddleware('form', 'poke:force');

        $this->get('form')->assertSee('start-poke-script');
    }

    public function test_forced_injection_only_renders_once(): void
    {
        $this->addMiddleware('form-with-component', 'poke:force');

        static::assertSame(1, substr_count($this->get('form-with-component')->getContent(), 'start-poke-script'));
    }

    public function test_forced_injection_on_no_form(): void
    {
        $this->addMiddleware('no-form', 'poke:force');

        $this->get('no-form')->assertSee('start-poke-script');
    }

    public function test_forced_injection_failed_without_body_tag(): void
    {
        $this->addMiddleware('no-end-body', 'poke:force');

        $this->get('no-end-body')->assertDontSee('start-poke-script');
    }

    public function test_forced_injection_failed_on_error(): void
    {
        $this->addMiddleware('error', 'poke:force');

        $this->get('error')->assertDontSee('start-poke-script');
    }

    public function test_forced_injection_failed_on_json(): void
    {
        $this->addMiddleware('form', 'poke:force');

        $this->getJson('form')->assertDontSee('start-poke-script');
    }
}