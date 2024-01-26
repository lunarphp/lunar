<?php

namespace Lunar\Tests\Shipping;

use Illuminate\Support\Facades\Config;
use Lunar\Shipping\ShippingServiceProvider;
use Lunar\Tests\Admin\Stubs\User;

class TestCase extends \Lunar\Tests\Admin\TestCase
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
        return [
            ...parent::getPackageProviders($app),
            ShippingServiceProvider::class,
        ];
    }
}
