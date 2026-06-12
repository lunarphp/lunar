<?php

namespace Lunar\Checkout\Actions;

use Illuminate\Contracts\Events\Dispatcher;
use Lunar\Checkout\Contracts\Actions\InvalidatesCheckoutSession;
use Lunar\Checkout\Events\CheckoutSessionInvalidated;
use Lunar\Checkout\Models\CheckoutSession;
use Lunar\Checkout\States\CheckoutSession\Cancelled;
use Lunar\Checkout\States\CheckoutSession\Open;
use Lunar\Checkout\States\CheckoutSession\PaymentProcessing;
use Lunar\Checkout\Support\PaymentIntentGateway;

/**
 * Void-first invalidation (spec 0010 §F). No transition to `Cancelled` ever
 * leaves an in-flight intent behind: when the session carries one, the gateway
 * must confirm the void first — an unconfirmable void blocks the terminal
 * transition and the session stays put for bounded reconciliation.
 */
final class InvalidateCheckoutSession implements InvalidatesCheckoutSession
{
    public function __construct(
        private PaymentIntentGateway $gateways,
        private Dispatcher $events,
    ) {}

    public function execute(CheckoutSession $session, string $reason): bool
    {
        if ($reference = $session->payment_intent_ref) {
            $gateway = $this->gateways->for($session);

            if ($gateway === null) {
                return false;
            }

            try {
                $gateway->voidIntent($reference);
            } catch (\Throwable) {
                // Unknown outcome is not a void — the charge may still land.
                return false;
            }
        }

        $transitioned = $session->transitionGuarded(
            [Open::$name, PaymentProcessing::$name],
            Cancelled::$name,
            [
                'active_cart_reference' => null,
                'cancelled_at' => now(),
            ],
        );

        if (! $transitioned) {
            return false;
        }

        $session->meta = array_merge(
            (array) ($session->meta?->getArrayCopy() ?? []),
            ['invalidation_reason' => $reason],
        );
        $session->save();

        $this->events->dispatch(new CheckoutSessionInvalidated($session, $reason));

        return true;
    }
}
