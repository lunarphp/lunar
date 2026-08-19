<?php

namespace Lunar\Checkout\PaymentMethods;

use Lunar\Checkout\Contracts\PaymentMethod;
use Lunar\Core\Models\Cart;

/**
 * Base for payment methods (spec 0002 §A). A gateway declares only what
 * differs — typically handle(), label(), driver() and component() — and
 * inherits the common defaults: needs an intent, always available, no express
 * placement, no client config.
 *
 * Gate a method on the basket by overriding isAvailable(). A method only valid
 * for orders being collected, for instance, reads the applied shipping option:
 *
 *     public function isAvailable(Cart $cart): bool
 *     {
 *         return (bool) $cart->getShippingOption()?->collect;
 *     }
 */
abstract class AbstractPaymentMethod implements PaymentMethod
{
    public function requiresIntent(): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function config(): array
    {
        return [];
    }

    public function isAvailable(Cart $cart): bool
    {
        return true;
    }

    public function supportsExpress(): bool
    {
        return false;
    }

    public function expressComponent(): ?string
    {
        return null;
    }
}
