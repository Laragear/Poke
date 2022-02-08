<?php

namespace Laragear\Poke;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class PokeServiceProvider extends ServiceProvider
{
    /**
     * Location of the main config file.
     *
     * @var string
     */
    public const CONFIG = __DIR__.'/../config/poke.php';

    /**
     * Location of the view directory.
     *
     * @var string
     */
    public const VIEWS = __DIR__ . '/../resources/views';

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(static::CONFIG, 'poke');
        $this->loadViewsFrom(static::VIEWS, 'laragear.poke');

        $this->app->singleton(Views\Components\Script::class);

        $this->app->singleton(
            Http\Middleware\InjectScript::class,
            static function (Application $app): Http\Middleware\InjectScript {
                return new Http\Middleware\InjectScript($app->make('config')->get('poke.mode'));
            }
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @param  \Illuminate\Contracts\Config\Repository  $config
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function boot(Router $router, Repository $config): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/poke.php');

        $this->bootMiddleware($router, $config);

        if ($this->app->runningInConsole()) {
            $this->publishes([static::CONFIG => $this->app->configPath('poke.php')], 'config');
            $this->publishes([static::VIEWS => $this->app->viewPath('vendor/laragear/poke')], 'views');
        }

        $this->loadViewComponentsAs('laragear.poke', [
            Views\Components\Script::class
        ]);
    }

    /**
     * Registers (or push globally) the Middleware
     *
     * @param  \Illuminate\Routing\Router  $router
     * @param  \Illuminate\Contracts\Config\Repository  $config
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function bootMiddleware(Router $router, Repository $config): void
    {
        $router->aliasMiddleware('poke', Http\Middleware\InjectScript::class);

        // If Larapoke is set to auto, push it as global middleware.
        if ($config->get('poke.mode') === 'auto') {
            $this->app->make(Kernel::class)->appendMiddlewareToGroup('web', Http\Middleware\InjectScript::class);
        }
    }
}