<?php

namespace Lunar\Tests;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Config;
use Lunar\Facades\Taxes;
use Lunar\LunarServiceProvider;
use Lunar\Tests\Core\Stubs\TestTaxDriver;
use Lunar\Tests\Core\Stubs\TestUrlGenerator;
use Lunar\Tests\Core\Stubs\User;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase;
use Spatie\LaravelBlink\BlinkServiceProvider;

class LunarTestCase extends TestCase
{
    use LazilyRefreshDatabase, WithWorkbench;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations();

        Config::set('providers.users.model', User::class);
        Config::set('lunar.urls.generator', TestUrlGenerator::class);
        Config::set('lunar.taxes.driver', 'test');

        Taxes::extend('test', function ($app) {
            return $app->make(TestTaxDriver::class);
        });

        activity()->disableLogging();

        // Freeze time to avoid timestamp errors
        $this->freezeTime();
    }

    protected function getPackageProviders($app): array
    {
        return [
            LunarServiceProvider::class,
            BlinkServiceProvider::class,
        ];
    }
}
