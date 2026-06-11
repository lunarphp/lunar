<?php

namespace Lunar\Checkout\States\CheckoutSession;

class Cancelled extends CheckoutSessionState
{
    public static string $name = 'cancelled';

    public function label(): string
    {
        return __('lunar-checkout::checkout.states.checkout-session.cancelled');
    }
}
