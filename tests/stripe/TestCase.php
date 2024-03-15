<?php

namespace Lunar\Tests\Stripe;

use Cartalyst\Converter\Laravel\ConverterServiceProvider;
use Illuminate\Support\Facades\Config;
use Kalnoy\Nestedset\NestedSetServiceProvider;
use Livewire\LivewireServiceProvider;
use Lunar\LunarServiceProvider;
use Lunar\Stripe\StripePaymentsServiceProvider;
use Lunar\Tests\Core\Stubs\User;
use Lunar\Tests\LunarTestCase;
use Lunar\Tests\Stripe\Stripe\MockClient;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\LaravelBlink\BlinkServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;
use Stripe\ApiRequestor;

class TestCase extends LunarTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // additional setup
        Config::set('providers.users.model', User::class);
        Config::set('services.stripe.key', 'SK_TESTER');
        Config::set('services.stripe.webhooks.payment_intent', 'FOOBAR');

        activity()->disableLogging();

        $mockClient = new MockClient();
        ApiRequestor::setHttpClient($mockClient);
    }

    protected function getPackageProviders($app): array
    {
        return array_merge(parent::getPackageProviders($app), [
            StripePaymentsServiceProvider::class,
            LivewireServiceProvider::class,
            MediaLibraryServiceProvider::class,
            ActivitylogServiceProvider::class,
            ConverterServiceProvider::class,
            NestedSetServiceProvider::class,
        ]);
    }
}
