<?php

namespace Laragear\Poke\Http;

use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository as ConfigContract;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Laragear\Poke\Http\Controllers\PokeController;

class RouteConstructor
{
    /**
     * Create a new Route Generator instance.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @param  \Illuminate\Contracts\Config\Repository  $config
     */
    public function __construct(protected Router $router, protected ConfigContract $config)
    {
        //
    }

    /**
     * Construct the Poke route.
     *
     * @return void
     */
    public function register(): void
    {
        $config = $this->parseConfig();

        // If the developer has set the route as `null` or `false`, bail.
        if (! $config['poke.poking.route']) {
            return;
        }

        $route = $this->route($config);

        // If a subdomain has been set, use it.
        if ($config['poke.poking.domain']) {
            $route->domain($config['poke.poking.domain']);
        }
    }

    /**
     * Parses the configuration.
     *
     * @return array
     */
    protected function parseConfig(): array
    {
        return $this->config->get([
            'poke.poking.route',
            'poke.poking.name',
            'poke.poking.domain',
            'poke.poking.middleware',
        ]);
    }

    /**
     * Returns a Poke route.
     *
     * @param  array  $config
     * @return \Illuminate\Routing\Route
     */
    protected function route(array $config): Route
    {
        return $this->router
            ->match('head', $config['poke.poking.route'])
            ->uses(PokeController::class)
            ->middleware($config['poke.poking.middleware'])
            ->name($config['poke.poking.name']);
    }

    /**
     * Create a new instance.
     *
     * @return void
     */
    public static function construct(): void
    {
        Container::getInstance()->make(__CLASS__)->register();
    }
}
