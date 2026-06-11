<?php

namespace Lunar\Checkout\States\CheckoutSession;

use Lunar\Checkout\Contracts\CheckoutSessionStateConfig;

class DefaultCheckoutSessionStateConfig implements CheckoutSessionStateConfig
{
    public function states(): array
    {
        return [
            Open::class,
            PaymentProcessing::class,
            Completed::class,
            Expired::class,
            Cancelled::class,
        ];
    }

    public function transitions(): array
    {
        return [
            // `Completed` is reachable from both Open (synchronous) and
            // PaymentProcessing (async, post-confirmation).
            Open::class => [Completed::class, PaymentProcessing::class, Cancelled::class, Expired::class],
            PaymentProcessing::class => [Completed::class, Open::class, Cancelled::class],
            Completed::class => [],
            Expired::class => [],
            Cancelled::class => [],
        ];
    }

    public function defaultState(): string
    {
        return Open::class;
    }
}
