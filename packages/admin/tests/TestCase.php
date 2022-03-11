<?php

namespace GetCandy\Hub\Tests;

use Cartalyst\Converter\Laravel\ConverterServiceProvider;
use GetCandy\GetCandyServiceProvider;
use GetCandy\Hub\AdminHubServiceProvider;
use Illuminate\Support\Facades\Config;
use Kalnoy\Nestedset\NestedSetServiceProvider;
use Livewire\LivewireServiceProvider;
use LivewireUI\Modal\LivewireModalServiceProvider;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        // additional setup
        Config::set('auth.guards.staff', [
            'driver' => 'getcandyhub',
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [
            GetCandyServiceProvider::class,
            LivewireServiceProvider::class,
            AdminHubServiceProvider::class,
            ActivitylogServiceProvider::class,
            MediaLibraryServiceProvider::class,
            ConverterServiceProvider::class,
            NestedSetServiceProvider::class,
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
