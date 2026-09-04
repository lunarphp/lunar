<?php

namespace Lunar\Tests\Api\Fixtures;

use Lunar\Tests\Api\TestCase;

/** The storefront with a host guard configured, so the customer area registers. */
class CustomerGuardTestCase extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('lunar.api.storefront.guard', 'web');
    }
}
