<?php

namespace Lunar\Tests\Panel\Fixtures;

use Lunar\Tests\Panel\Fixtures\Customers\CustomerFixtureServiceProvider;
use Lunar\Tests\Panel\TestCase;

class CustomerFixtureTestCase extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ...parent::getPackageProviders($app),
            CustomerFixtureServiceProvider::class,
        ];
    }
}
