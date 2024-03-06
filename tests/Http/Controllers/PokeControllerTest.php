<?php

namespace Tests\Http\Controllers;

use Symfony\Component\HttpFoundation\Cookie;
use Tests\TestCase;

use function now;

class PokeControllerTest extends TestCase
{
    public function test_sets_xsrf_token(): void
    {
        static::assertInstanceOf(Cookie::class, $this->call('HEAD', 'poke')->getCookie('XSRF-TOKEN'));
    }

    public function test_extends_csrf_token_lifetime(): void
    {
        $this->travelTo(now());

        $first = $this->call('HEAD', 'poke')->getCookie('XSRF-TOKEN')->getExpiresTime();

        $this->travelTo(now()->addHour());

        $second = $this->call('HEAD', 'poke')->getCookie('XSRF-TOKEN')->getExpiresTime();

        static::assertSame(60 * 60, $second - $first);
    }

    public function test_extends_session_lifetime(): void
    {
        $name = $this->app->make('config')->get('session.cookie');

        $this->travelTo(now());

        $first = $this->call('HEAD', 'poke')->getCookie($name)->getExpiresTime();

        $this->travelTo(now()->addHour());

        $second = $this->call('HEAD', 'poke')->getCookie($name)->getExpiresTime();

        static::assertSame(60 * 60, $second - $first);
    }

    public function test_responds_with_no_content(): void
    {
        $this->call('HEAD', 'poke')
            ->assertNoContent()
            ->assertStatus(204);
    }

    public function test_wrong_method_gives_405(): void
    {
        foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] as $method) {
            $this->call($method, 'poke')->assertStatus(405);
        }
    }
}
