<?php

namespace Laragear\Poke\Blade\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use function config;
use function url;
use function view;

class Script extends Component
{
    /**
     * Create a new component instance.
     *
     * @param  bool  $force
     */
    public function __construct(protected bool $force = false)
    {
        //
    }

    /**
     * Get the view / view contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render(): View|string
    {
        $config = config()->get([
            'session.lifetime',
            'poke.mode',
            'poke.poking.route',
            'poke.times'
        ]);

        if ($config['poke.mode'] !== 'blade' && ! $this->force) {
            return '';
        }

        $session = $config['session.lifetime'] * 60 * 1000;

        return view('poke::script', [
            'route' => url($config['poke.poking.route']),
            'interval' => (int) ($session / $config['poke.times']),
            'lifetime' => $session,
        ]);
    }
}
