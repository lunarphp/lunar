<?php

namespace Lunar\Checkout\States\CheckoutSession;

class Expired extends CheckoutSessionState
{
    public static string $name = 'expired';

    public function label(): string
    {
        return __('lunar-checkout::checkout.states.checkout-session.expired');
    }
}
