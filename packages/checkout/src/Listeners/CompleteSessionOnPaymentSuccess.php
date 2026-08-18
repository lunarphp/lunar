<?php

namespace Lunar\Checkout\Listeners;

use Lunar\Checkout\Contracts\CheckoutDriver;
use Lunar\Checkout\Models\CheckoutSession;
use Lunar\Checkout\States\CheckoutSession\PaymentProcessing;
use Lunar\Core\Events\PaymentAttemptEvent;
use Lunar\Core\Models\Order;

/**
 * The gateway-agnostic success bridge (spec 0002 §D): any gateway driver that
 * authorises a payment dispatches PaymentAttemptEvent; when the paid order's
 * cart has a session pinned at the pay boundary, the checkout driver completes
 * it — session → Completed, order adopted as its order_reference.
 *
 * complete() is idempotent (terminal short-circuit + guarded transition), so
 * webhook replays, the reconciliation sweep, and this listener can all race
 * safely — exactly one completion wins.
 */
class CompleteSessionOnPaymentSuccess
{
    public function __construct(
        private readonly CheckoutDriver $driver,
    ) {}

    public function handle(PaymentAttemptEvent $event): void
    {
        $authorize = $event->paymentAuthorize;

        if (! $authorize->success || $authorize->orderId === null) {
            return;
        }

        $cartId = Order::query()->whereKey($authorize->orderId)->value('cart_id');

        if ($cartId === null) {
            return;
        }

        $session = CheckoutSession::query()
            ->where('cart_reference', (string) $cartId)
            ->where('status', PaymentProcessing::$name)
            ->first();

        if ($session === null) {
            return;
        }

        $this->driver->complete($session);
    }
}
