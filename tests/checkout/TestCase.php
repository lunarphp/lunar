<?php

namespace Lunar\Tests\Checkout;

use Kalnoy\Nestedset\NestedSetServiceProvider;
use Lunar\Checkout\CheckoutServiceProvider;
use Lunar\Core\LunarServiceProvider;
use Lunar\Tests\TestCase as BaseTestCase;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\LaravelBlink\BlinkServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;
use Spatie\Permission\PermissionServiceProvider;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            LunarServiceProvider::class,
            BlinkServiceProvider::class,
            CheckoutServiceProvider::class,
            MediaLibraryServiceProvider::class,
            PermissionServiceProvider::class,
            ActivitylogServiceProvider::class,
            NestedSetServiceProvider::class,
        ];
    }
}
