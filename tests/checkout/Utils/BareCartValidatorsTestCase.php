<?php

namespace Lunar\Tests\Checkout\Utils;

use Lunar\Tests\Checkout\TestCase;

/**
 * A host that publishes `lunar/cart.php` without a `validators.order_create`
 * key (or a core version that moves it) leaves the package appending to
 * nothing. Emptying the whole `validators` bag before the providers register
 * reproduces that: `mergeConfigFrom` merges only the top level of
 * `lunar.cart`, so the pre-set empty bag wins over core's defaults.
 */
class BareCartValidatorsTestCase extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('lunar.cart.validators', []);
    }
}
