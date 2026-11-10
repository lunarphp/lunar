<?php

namespace Lunar\Tests\Shipping;

use Illuminate\Support\Facades\Config;
use Lunar\LunarServiceProvider;
use Lunar\Nestedset\NestedSetServiceProvider;
use Lunar\Shipping\ShippingServiceProvider;
use Lunar\Tests\Admin\Stubs\User;
use Lunar\Tests\TestCase as BaseTestCase;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\LaravelBlink\BlinkServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;

class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // additional setup
        Config::set('providers.users.model', User::class);
        Config::set('lunar.urls.generator', null);
        activity()->disableLogging();

    }

    protected function getPackageProviders($app)
    {
        return [
            LunarServiceProvider::class,
            MediaLibraryServiceProvider::class,
            ActivitylogServiceProvider::class,
            NestedSetServiceProvider::class,
            ShippingServiceProvider::class,
            BlinkServiceProvider::class,
        ];
    }
}
