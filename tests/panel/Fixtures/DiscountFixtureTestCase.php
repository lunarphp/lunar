<?php

namespace Lunar\Tests\Panel\Fixtures;

use Lunar\Tests\Panel\Fixtures\Discounts\DiscountFixtureServiceProvider;
use Lunar\Tests\Panel\TestCase;

class DiscountFixtureTestCase extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ...parent::getPackageProviders($app),
            DiscountFixtureServiceProvider::class,
        ];
    }
}
