<?php

namespace Tests;

use Laragear\Poke\PokeServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [PokeServiceProvider::class];
    }
}
