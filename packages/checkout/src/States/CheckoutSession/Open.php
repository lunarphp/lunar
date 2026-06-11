<?php

namespace Lunar\Checkout\States\CheckoutSession;

class Open extends CheckoutSessionState
{
    public static string $name = 'open';

    public function label(): string
    {
        return __('lunar-checkout::checkout.states.checkout-session.open');
    }
}
