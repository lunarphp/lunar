<?php

namespace Lunar\Checkout\States\CheckoutSession;

class Completed extends CheckoutSessionState
{
    public static string $name = 'completed';

    public function label(): string
    {
        return __('lunar-checkout::checkout.states.checkout-session.completed');
    }
}
