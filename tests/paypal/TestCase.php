<?php

namespace Lunar\Tests\Paypal;

use Illuminate\Support\Facades\Config;
use Kalnoy\Nestedset\NestedSetServiceProvider;
use Lunar\LunarServiceProvider;
use Lunar\Paypal\PaypalServiceProvider;
use Lunar\Tests\Core\Stubs\User;
use Lunar\Tests\TestCase as BaseTestCase;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\LaravelBlink\BlinkServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;

class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('auth.providers.users.model', User::class);

        activity()->disableLogging();
    }

    protected function getPackageProviders($app)
    {
        return [
            LunarServiceProvider::class,
            PaypalServiceProvider::class,
            BlinkServiceProvider::class,
            MediaLibraryServiceProvider::class,
            ActivitylogServiceProvider::class,
            NestedSetServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $this->replaceModelsForTesting();
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();
    }
}
