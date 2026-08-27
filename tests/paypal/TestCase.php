<?php

namespace Lunar\Tests\Paypal;

use Illuminate\Support\Facades\Config;
use Livewire\LivewireServiceProvider;
use Lunar\Core\LunarServiceProvider;
use Lunar\Nestedset\NestedSetServiceProvider;
use Lunar\Paypal\PaypalServiceProvider;
use Lunar\Tests\Core\Stubs\User;
use Lunar\Tests\TestCase as BaseTestCase;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\LaravelBlink\BlinkServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;
use Spatie\Permission\PermissionServiceProvider;

class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('auth.providers.users.model', User::class);
        // The driver reads credentials from `services.paypal.*` today; spec 0071
        // slice 3 consolidates these onto `lunar.paypal.*`.
        Config::set('services.paypal.client_id', 'TEST_CLIENT_ID');
        Config::set('services.paypal.secret', 'TEST_SECRET');
        Config::set('services.paypal.env', 'sandbox');

        activity()->disableLogging();
    }

    protected function defineRoutes($router)
    {
        // buildInitialOrder() links its return/cancel URLs at these routes.
        $router->get('checkout/success', fn () => 'ok')->name('checkout.success');
        $router->get('checkout/cancel', fn () => 'cancelled')->name('checkout.cancel');
    }

    protected function getPackageProviders($app)
    {
        return [
            LunarServiceProvider::class,
            BlinkServiceProvider::class,
            PaypalServiceProvider::class,
            LivewireServiceProvider::class,
            MediaLibraryServiceProvider::class,
            PermissionServiceProvider::class,
            ActivitylogServiceProvider::class,
            NestedSetServiceProvider::class,
        ];
    }
}
