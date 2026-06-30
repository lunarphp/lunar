<?php

namespace Lunar\Core\Actions\Orders;

use Lunar\Core\Contracts\Actions\Orders\ResolvesPaymentStatus;
use Lunar\Core\Models\Order;
use Lunar\Core\States\Order\Payment\Authorized;
use Lunar\Core\States\Order\Payment\Paid;
use Lunar\Core\States\Order\Payment\PartiallyPaid;
use Lunar\Core\States\Order\Payment\PartiallyRefunded;
use Lunar\Core\States\Order\Payment\Pending;
use Lunar\Core\States\Order\Payment\Refunded;
use Lunar\Core\States\Order\Payment\Voided;

/**
 * Roll the transaction ledger up into a Shopify-style `financial_status`.
 *
 * Amounts are integer minor units compared against `order.total`:
 *   captured  = sum of successful captures
 *   refunded  = sum of successful refunds
 *   authorized = a successful payment intent exists
 */
class ResolvePaymentStatus implements ResolvesPaymentStatus
{
    public function execute(Order $order): string
    {
        /** @var Order $order */
        $captured = (int) $order->captures()->whereSuccess(true)->sum('amount');
        $refunded = (int) $order->refunds()->whereSuccess(true)->sum('amount');
        $authorized = $order->intents()->whereSuccess(true)->exists();
        $hasTransactions = $order->transactions()->exists();
        $total = (int) $order->total;

        return match (true) {
            $captured > 0 && $refunded >= $captured => Refunded::class,
            $captured >= $total && $refunded > 0 => PartiallyRefunded::class,
            $captured >= $total && $captured > 0 => Paid::class,
            // A zero-total order with no refunds settles as paid.
            $total === 0 && $refunded === 0 => Paid::class,
            $captured > 0 => PartiallyPaid::class,
            $authorized => Authorized::class,
            $hasTransactions => Voided::class,
            default => Pending::class,
        };
    }
}
