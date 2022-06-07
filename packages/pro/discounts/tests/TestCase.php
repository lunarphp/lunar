<?php

namespace GetCandy\Discounts\Tests;

use Cartalyst\Converter\Laravel\ConverterServiceProvider;
use GetCandy\Discounts\DiscountsServiceProvider;
use GetCandy\GetCandyServiceProvider;
use GetCandy\Hub\AdminHubServiceProvider;
use GetCandy\Hub\Tests\Stubs\User;
use GetCandy\Shipping\ShippingServiceProvider;
use GetCandy\Tests\Stubs\TestUrlGenerator;
use Illuminate\Support\Facades\Config;
use Kalnoy\Nestedset\NestedSetServiceProvider;
use Livewire\LivewireServiceProvider;
use Spatie\Activitylog\ActivitylogServiceProvider;
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
            LivewireServiceProvider::class,
            MediaLibraryServiceProvider::class,
            ActivitylogServiceProvider::class,
            ConverterServiceProvider::class,
            NestedSetServiceProvider::class,
            DiscountsServiceProvider::class,
            AdminHubServiceProvider::class,
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
