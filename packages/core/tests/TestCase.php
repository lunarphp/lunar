<?php

namespace Lunar\Tests;

use Cartalyst\Converter\Laravel\ConverterServiceProvider;
use Lunar\GetCandyServiceProvider;
use Lunar\Tests\Stubs\TestUrlGenerator;
use Lunar\Tests\Stubs\User;
use Illuminate\Support\Facades\Config;
use Kalnoy\Nestedset\NestedSetServiceProvider;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\LaravelBlink\BlinkServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        // additional setup
        Config::set('providers.users.model', User::class);
        Config::set('getcandy.urls.generator', TestUrlGenerator::class);
        activity()->disableLogging();
    }

    protected function getPackageProviders($app)
    {
        return [
            GetCandyServiceProvider::class,
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
