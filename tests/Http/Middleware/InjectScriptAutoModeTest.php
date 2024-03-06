<?php

namespace Tests\Http\Middleware;

use Tests\TestCase;

use function substr_count;

class InjectScriptAutoModeTest extends TestCase
{
    use DefinesWebRoutes;

    public function test_injects_script_with_csrf_input_present(): void
    {
        $this->get('form')->assertSee('start-poke-script');
    }

    public function test_injects_script_before_closing_body_tag(): void
    {
        $this->get('form')->assertSee(<<<'BODYEND'
        // end-poke-script
    </script>
</body>
BODYEND
            , false);
    }

    public function test_injects_script_only_once(): void
    {
        static::assertSame(1, substr_count($this->get('form-multiple')->getContent(), 'start-poke-script'));
    }

    public function test_injects_only_once_with_not_forced_component(): void
    {
        static::assertSame(1, substr_count($this->get('form-with-component')->getContent(), 'start-poke-script'));
    }

    public function test_doesnt_inject_script_without_csrf_input(): void
    {
        $this->get('no-form')->assertDontSee('start-poke-script');
    }

    public function test_doesnt_inject_script_without_closing_body_tag(): void
    {
        $this->get('no-end-body')->assertDontSee('start-poke-script');
    }

    public function test_doesnt_injects_on_error(): void
    {
        $this->get('error')->assertDontSee('start-poke-script');
    }

    public function test_doesnt_injects_on_redirect(): void
    {
        $this->get('redirect')->assertRedirect()->assertDontSee('start-poke-script');
    }

    public function test_doesnt_injects_on_json(): void
    {
        $this->getJson('form')->assertDontSee('start-poke-script');
    }
}
