<?php

namespace Lunar\Stripe\Checkout;

use Lunar\Checkout\PaymentMethods\AbstractPaymentMethod;

/**
 * Card payments through Stripe, as a checkout payment method (spec 0002).
 * Registered by the host app — installing lunar/stripe alone enables nothing;
 * lunar/checkout is a suggested dependency, referenced only when a host that
 * has both registers this class.
 */
class StripeCardMethod extends AbstractPaymentMethod
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
}
