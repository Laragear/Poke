<?php

namespace Tests\Filament;

use Filament\Actions\ActionsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\FormsServiceProvider;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Schemas\Schema;
use Filament\Support\SupportServiceProvider;
use Filament\Support\View\ViewManager;
use Filament\View\PanelsRenderHook;
use Laragear\Poke\Livewire\InteractsWithPoke;
use Livewire\Livewire;
use Livewire\LivewireServiceProvider;
use Tests\TestCase;

use function class_exists;

class PokeTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ...parent::getPackageProviders($app),
            FilamentServiceProvider::class,
            SupportServiceProvider::class,
            FormsServiceProvider::class,
            ActionsServiceProvider::class,
            LivewireServiceProvider::class,
            TestPanel::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [Livewire::class];
    }

    protected function setUp(): void
    {
        $this->markTestSkippedUnless(class_exists(FilamentServiceProvider::class), 'Filament 5.x is not installed');

        parent::setUp();
    }

    public function test_registers_body_end_hook(): void
    {
        $hook = $this->app->make(ViewManager::class)->renderHook(PanelsRenderHook::BODY_END);

        static::assertStringContainsString('// start-poke-script', $hook);
    }

    public function test_dispatches_check_when_component_uses_form(): void
    {
        Livewire::test(TestPageWithForms::class)
            ->assertDispatched('poke:renew');
    }

    public function test_does_not_dispatches_check_when_component_not_uses_form(): void
    {
        Livewire::test(TestPage::class)
            ->assertNotDispatched('poke:renew');
    }

    public function test_dispatches_with_livewire_trait(): void
    {
        Livewire::test(TestPageWithTraitPoke::class)
            ->assertDispatched('poke:renew');
    }
}

if (class_exists(FilamentServiceProvider::class)) {
    class TestPage extends Page
    {
        use InteractsWithForms;

        protected string $view = 'filament-panels::pages.simple';

        public function hasLogo()
        {
            return false;
        }
    }

    class TestPageWithForms extends TestPage implements HasForms
    {
        //
    }

    class TestPageWithTraitPoke extends TestPage
    {
        use InteractsWithPoke;

        public function content(Schema $schema): Schema
        {
            return $schema->components([]);
        }
    }

    class TestPanel extends PanelProvider
    {
        public function panel(Panel $panel): Panel
        {
            return $panel
                ->default()
                ->id('test')
                ->path('app')
                ->pages([
                    TestPage::class,
                    TestPageWithForms::class,
                    TestPageWithTraitPoke::class,
                ]);
        }
    }
}
