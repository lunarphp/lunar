<?php

namespace Lunar\Checkout\Drivers;

use Lunar\Checkout\Contracts\Actions\CreatesCheckoutSession;
use Lunar\Checkout\Contracts\CheckoutDriver;
use Lunar\Checkout\Models\CheckoutSession;
use Lunar\Core\Models\Cart;

/**
 * The default checkout driver — ingests a Lunar {@see Cart} into a session and
 * (eventually) finalises it into a Lunar order.
 */
class LunarCheckoutDriver implements CheckoutDriver
{
    public function __construct(
        private CreatesCheckoutSession $createCheckoutSession,
    ) {}

    public function createSession(mixed $source): CheckoutSession
    {
        if (! $source instanceof Cart) {
            throw new \InvalidArgumentException(
                'The lunar checkout driver expects a ['.Cart::class.'] source, ['.get_debug_type($source).'] given.'
            );
        }

        return $this->createCheckoutSession->execute($source);
    }

    public function complete(CheckoutSession $session): mixed
    {
        // Spec 0004 §E — Lunar order creation + AwaitingPayment → InProcess.
        // Implemented alongside the payment layer (spec 0002).
        throw new \RuntimeException('LunarCheckoutDriver::complete() is not yet implemented.');
    }
}
