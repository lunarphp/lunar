<?php

namespace Lunar\Tests\Search;

use Cartalyst\Converter\Laravel\ConverterServiceProvider;
use Illuminate\Support\Facades\Config;
use Laravel\Scout\ScoutServiceProvider;
use Lunar\LunarServiceProvider;
use Lunar\Search\SearchServiceProvider;
use Lunar\Stripe\Facades\Stripe;
use Lunar\Tests\Stubs\User;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\LaravelData\LaravelDataServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
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
            ConverterServiceProvider::class,
            ActivitylogServiceProvider::class,
            LaravelDataServiceProvider::class,
            SearchServiceProvider::class,
            ScoutServiceProvider::class,
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
