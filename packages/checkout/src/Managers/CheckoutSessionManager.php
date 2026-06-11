<?php

namespace Lunar\Checkout\Managers;

use Illuminate\Support\Manager;
use Lunar\Checkout\Contracts\CheckoutDriver;
use Lunar\Checkout\Drivers\LunarCheckoutDriver;

/**
 * Resolves the active checkout driver by name from `config('lunar.checkout.driver')`
 * (default `lunar`) — the standard Laravel Manager pattern. Packages register
 * their own backend with `extend('statamic', fn () => …)`.
 *
 * Distinct from the element-composing `CheckoutManager` (spec 0001): this one
 * ingests carts and finalises orders.
 */
class CheckoutSessionManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return $this->config->get('lunar.checkout.driver', 'lunar');
    }

    public function createLunarDriver(): CheckoutDriver
    {
        return $this->container->make(LunarCheckoutDriver::class);
    }
}
