<?php

namespace Lunar\Tests\Checkout;

use Inertia\ServiceProvider as InertiaServiceProvider;
use Lunar\Checkout\CheckoutServiceProvider;
use Lunar\Core\LunarServiceProvider;
use Lunar\Nestedset\NestedSetServiceProvider;
use Lunar\Tests\TestCase as BaseTestCase;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\LaravelBlink\BlinkServiceProvider;
use Spatie\LaravelData\LaravelDataServiceProvider;
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
            // The checkout serves its own Inertia app and start() returns an
            // Inertia location redirect — its provider registers the
            // Request::inertia() macro the response relies on.
            InertiaServiceProvider::class,
            MediaLibraryServiceProvider::class,
            PermissionServiceProvider::class,
            ActivitylogServiceProvider::class,
            NestedSetServiceProvider::class,
            // Projected DTOs (addresses, snapshots) are spatie/laravel-data
            // objects; Inertia's prop resolver reads this provider's config
            // when it serializes them.
            LaravelDataServiceProvider::class,
        ];
    }
}
