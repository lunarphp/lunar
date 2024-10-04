<?php

namespace Lunar\Tests\Search;

use Cartalyst\Converter\Laravel\ConverterServiceProvider;
use Kalnoy\Nestedset\NestedSetServiceProvider;
use Laravel\Scout\ScoutServiceProvider;
use Lunar\LunarServiceProvider;
use Lunar\Search\SearchServiceProvider;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\LaravelBlink\BlinkServiceProvider;
use Spatie\LaravelData\LaravelDataServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // additional setup
        //        Config::set('providers.users.model', User::class);
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
            ScoutServiceProvider::class,
            LaravelDataServiceProvider::class,
            SearchServiceProvider::class
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // perform environment setup
    }
}
