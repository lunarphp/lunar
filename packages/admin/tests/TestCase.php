<?php

namespace Lunar\Hub\Tests;

use Cartalyst\Converter\Laravel\ConverterServiceProvider;
use Lunar\LunarServiceProvider;
use Lunar\Hub\AdminHubServiceProvider;
use Lunar\Tests\Stubs\TestUrlGenerator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Kalnoy\Nestedset\NestedSetServiceProvider;
use Livewire\LivewireServiceProvider;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\LaravelBlink\BlinkServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        // additional setup
        Config::set('auth.guards.staff', [
            'driver' => 'lunarhub',
        ]);
        Config::set('lunar.urls.generator', TestUrlGenerator::class);

        View::addLocation(__DIR__.'/resources/views');
    }

    protected function getPackageProviders($app)
    {
        return [
            LunarServiceProvider::class,
            LivewireServiceProvider::class,
            AdminHubServiceProvider::class,
            ActivitylogServiceProvider::class,
            MediaLibraryServiceProvider::class,
            ConverterServiceProvider::class,
            NestedSetServiceProvider::class,
            BlinkServiceProvider::class,
        ];
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

    protected function getEnvironmentSetUp($app)
    {
        // perform environment setup
    }
}
