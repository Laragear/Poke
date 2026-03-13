<?php

namespace Tests\Livewire;

use Laragear\Poke\Livewire\InteractsWithPoke;
use Livewire\Component;
use Livewire\Livewire;
use Livewire\LivewireServiceProvider;
use Tests\TestCase;

class InteractsWithPokeTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            ...parent::getPackageProviders($app),
        ];
    }

    public function test_dispatches_event_on_render()
    {
        Livewire::test(TestComponentWithPoke::class)
            ->assertDispatched('poke:renew')
            ->assertStatus(200);
    }
}

class TestComponentWithPoke extends Component
{
    use InteractsWithPoke;

    public function render()
    {
        return '<div></div>';
    }
}
