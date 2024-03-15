<?php

namespace Lunar\Tests\Shipping;

use Cartalyst\Converter\Laravel\ConverterServiceProvider;
use Illuminate\Support\Facades\Config;
use Kalnoy\Nestedset\NestedSetServiceProvider;
use Lunar\LunarServiceProvider;
use Lunar\Shipping\ShippingServiceProvider;
use Lunar\Tests\Admin\Stubs\User;
use Lunar\Tests\LunarTestCase;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\LaravelBlink\BlinkServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;

class TestCase extends LunarTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // additional setup
        Config::set('providers.users.model', User::class);
        Config::set('lunar.urls.generator', null);
        activity()->disableLogging();

    }

    protected function getPackageProviders($app): array
    {
        return array_merge(parent::getPackageProviders($app), [
            MediaLibraryServiceProvider::class,
            ActivitylogServiceProvider::class,
            ConverterServiceProvider::class,
            NestedSetServiceProvider::class,
            ShippingServiceProvider::class,
        ]);
    }
}
