<?php

namespace Laragear\Poke;

use Filament\Support\View\ViewManager;
use Illuminate\Contracts\Config\Repository as ConfigContract;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Http\Kernel as HttpContract;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use Livewire\Livewire;
use function method_exists;

/**
 * @internal
 */
class PokeServiceProvider extends ServiceProvider
{
    public const string CONFIG = __DIR__.'/../config/poke.php';
    public const string VIEWS = __DIR__.'/../resources/views';

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(static::CONFIG, 'poke');

        $this->app->singleton(
            Http\Middleware\InjectScript::class,
            static function (Application $app): Http\Middleware\InjectScript {
                return new Http\Middleware\InjectScript($app->make('config')->get('poke.mode'));
            }
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(Router $router, ConfigContract $config): void
    {
        $this->loadViewsFrom(static::VIEWS, 'poke');
        $this->loadViewComponentsAs('poke', [Blade\Components\Script::class]);
        $this->loadRoutesFrom(__DIR__.'/../routes/poke.php');

        $router->aliasMiddleware('poke', Http\Middleware\InjectScript::class);

        // If Larapoke is set to auto, push it as global middleware.
        if (
            $config->get('poke.mode') === 'auto' &&
            method_exists($kernel = $this->app->make(HttpContract::class), 'appendMiddlewareToGroup')
        ) {
            $kernel->appendMiddlewareToGroup('web', Http\Middleware\InjectScript::class);
        }

        if ($this->app->runningInConsole()) {
            $this->publishes([static::CONFIG => $this->app->configPath('poke.php')], 'config');
            // @phpstan-ignore-next-line
            $this->publishes([static::VIEWS => $this->app->viewPath('vendor/poke')], 'views');
        }

        if ($this->app->bound(\Filament\Support\View\ViewManager::class)) {
            $this->registerFilamentHooks();
        }
    }

    /**
     * Registers Filament PHP Hooks for rendering.
     */
    protected function registerFilamentHooks(): void
    {
        $this->app->make(\Livewire\LivewireManager::class)->listen(
            'render',
            static function (\Livewire\Component $component): void {
                // Dispatch the event to the frontend if the Page implements Forms.
                if ($component instanceof \Filament\Forms\Contracts\HasForms) {
                    $component->dispatch('poke:renew');
                }
            }
        );

        $this->app->make(\Filament\Support\View\ViewManager::class)->registerRenderHook(
            \Filament\View\PanelsRenderHook::BODY_END,
            static function (): string {
                return BladeCompiler::render('<x-poke-script force />');
            }
        );
    }
}
