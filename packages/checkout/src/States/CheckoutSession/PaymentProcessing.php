<?php

namespace Lunar\Checkout\States\CheckoutSession;

class PaymentProcessing extends CheckoutSessionState
{
    public static string $name = 'payment-processing';

    public function label(): string
    {
        return __('lunar-checkout::checkout.states.checkout-session.payment-processing');
    }
}
