<?php

namespace Tests;

use Laragear\Poke\PokeServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app): array
    {
        return [PokeServiceProvider::class];
    }
}
