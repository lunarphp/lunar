<?php

namespace Lunar\Tests\DemoData;

use Lunar\Bundles\BundlesServiceProvider;

class BundlesTestCase extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [...parent::getPackageProviders($app), BundlesServiceProvider::class];
    }
}
