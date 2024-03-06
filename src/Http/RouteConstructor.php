<?php

namespace Laragear\Poke\Http;

use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository as ConfigContract;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Laragear\Poke\Http\Controllers\PokeController;

/**
 * @internal
 */
class RouteConstructor
{
    /**
     * Create a new Route Generator instance.
     */
    public function __construct(protected Router $router, protected ConfigContract $config)
    {
        //
    }

    /**
     * Construct the Poke route.
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
     * @return array{"poke.poking.route": string|null|false, "poke.poking.name": string, "poke.poking.domain": string, "poke.poking.middleware": string|string[]}
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
     * @param  array{"poke.poking.route": string|null|false, "poke.poking.name": string, "poke.poking.domain": string, "poke.poking.middleware": string|string[]}  $config
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
     */
    public static function construct(): void
    {
        Container::getInstance()->make(__CLASS__)->register();
    }
}
