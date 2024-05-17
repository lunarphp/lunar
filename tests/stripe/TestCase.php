<?php

namespace Lunar\Tests\Stripe;

use Cartalyst\Converter\Laravel\ConverterServiceProvider;
use Illuminate\Support\Facades\Config;
use Kalnoy\Nestedset\NestedSetServiceProvider;
use Livewire\LivewireServiceProvider;
use Lunar\LunarServiceProvider;
use Lunar\Stripe\Facades\Stripe;
use Lunar\Stripe\StripePaymentsServiceProvider;
use Lunar\Tests\Stubs\User;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\LaravelBlink\BlinkServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // additional setup
        Config::set('providers.users.model', User::class);
        Config::set('services.stripe.key', 'SK_TESTER');
        Config::set('services.stripe.webhooks.payment_intent', 'FOOBAR');

        activity()->disableLogging();

        Stripe::fake();
    }

    protected function getPackageProviders($app)
    {
        return [
            LunarServiceProvider::class,
            BlinkServiceProvider::class,
            StripePaymentsServiceProvider::class,
            LivewireServiceProvider::class,
            MediaLibraryServiceProvider::class,
            ActivitylogServiceProvider::class,
            ConverterServiceProvider::class,
            NestedSetServiceProvider::class,
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
