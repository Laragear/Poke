<?php

namespace Laragear\Poke\Livewire;

trait InteractsWithPoke
{
    /**
     * When the component renders, the event is dispatched to the frontend.
     */
    public function renderedInteractsWithPoke(): void
    {
        $this->dispatch('poke:renew');
    }
}
