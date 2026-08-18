<?php

namespace Lunar\Stripe\Checkout;

use Lunar\Checkout\Contracts\PaymentMethod;

/**
 * Card payments through Stripe, as a checkout payment method (spec 0002).
 * Registered by the host app — installing lunar/stripe alone enables nothing;
 * lunar/checkout is a suggested dependency, referenced only when a host that
 * has both registers this class.
 */
class StripeCardMethod implements PaymentMethod
{
    public function handle(): string
    {
        return 'card';
    }

    public function label(): string
    {
        return 'Card';
    }

    public function driver(): string
    {
        return 'stripe';
    }

    public function requiresIntent(): bool
    {
        return true;
    }

    public function component(): string
    {
        return 'stripe-card';
    }

    public function config(): array
    {
        return [
            'publishableKey' => (string) config('services.stripe.public_key'),
        ];
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
