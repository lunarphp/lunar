<?php

use Cartalyst\Converter\Laravel\ConverterServiceProvider;
use Illuminate\Support\Facades\Config;
use Kalnoy\Nestedset\NestedSetServiceProvider;
use Lunar\Facades\Taxes;
use Lunar\LunarServiceProvider;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\LaravelBlink\BlinkServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;
use Stubs\TestTaxDriver;
use Stubs\TestUrlGenerator;
use Stubs\User;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // additional setup
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

    protected function getPackageProviders($app)
    {
        return [
            LunarServiceProvider::class,
            MediaLibraryServiceProvider::class,
            ActivitylogServiceProvider::class,
            ConverterServiceProvider::class,
            NestedSetServiceProvider::class,
            BlinkServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // perform environment setup
    }

    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();
    }
}
