<?php

namespace Tests;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            'Laragear\Poke\PokeServiceProvider',
            'Laravel\Ui\UiServiceProvider' // Needed for auth scaffolding
        ];
    }
}