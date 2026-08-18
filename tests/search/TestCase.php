<?php

namespace Lunar\Tests\Search;

use Illuminate\Support\Facades\Config;
use Laravel\Scout\ScoutServiceProvider;
use Lunar\Core\LunarServiceProvider;
use Lunar\Nestedset\NestedSetServiceProvider;
use Lunar\Search\SearchServiceProvider;
use Lunar\Stripe\Facades\Stripe;
use Lunar\Tests\Stubs\User;
use Lunar\Tests\TestCase as BaseTestCase;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\LaravelBlink\BlinkServiceProvider;
use Spatie\LaravelData\LaravelDataServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;
use Spatie\Permission\PermissionServiceProvider;

class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // additional setup
        Config::set('providers.users.model', User::class);
        Config::set('services.stripe.key', 'SK_TESTER');
        Config::set('services.stripe.webhooks.lunar', 'FOOBAR');

        activity()->disableLogging();

        Stripe::fake();
    }

    protected function getPackageProviders($app)
    {
        return [
            LunarServiceProvider::class,
            MediaLibraryServiceProvider::class,
            NestedSetServiceProvider::class,
            BlinkServiceProvider::class,
            ActivitylogServiceProvider::class,
            LaravelDataServiceProvider::class,
            SearchServiceProvider::class,
            ScoutServiceProvider::class,
            PermissionServiceProvider::class,
        ];
    }
}
