<?php

namespace Lunar\Core\Actions\Orders;

use Illuminate\Support\Str;
use Lunar\Core\Contracts\Actions\Orders\RecordsPayment;
use Lunar\Core\Exceptions\OrderActionException;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Transaction;

/**
 * Record a payment taken after checkout (an invoice settled, cash at the
 * counter) as a successful offline capture. The transaction observer
 * recomputes the order's payment status.
 */
class RecordPayment implements RecordsPayment
{
    public function execute(Order $order, int $amount, ?string $reference = null, ?string $notes = null): Transaction
    {
        /** @var Order $order */
        return $order->getConnection()->transaction(function () use ($order, $amount, $reference, $notes) {
            // Lock the order so two payments recorded at once can't both pass the balance check.
            $order->newQuery()->whereKey($order->getKey())->lockForUpdate()->first();

            if ($amount <= 0) {
                throw new OrderActionException('Payment amount must be greater than zero.');
            }

            if ($amount > self::outstandingBalance($order)) {
                throw new OrderActionException('Payment amount exceeds the outstanding balance on this order.');
            }

            /** @var Transaction $transaction */
            $transaction = $order->transactions()->create([
                'type' => 'capture',
                'success' => true,
                'driver' => 'offline',
                'amount' => $amount,
                'reference' => $reference ?: (string) Str::uuid(),
                'status' => 'settled',
                'notes' => $notes,
                'captured_at' => now(),
            ]);

            return $transaction;
        });
    }

    /**
     * What the customer still owes, in minor units. Refunds don't add to it.
     */
    public static function outstandingBalance(Order $order): int
    {
        /** @var Order $order */
        $captured = (int) $order->captures()->whereSuccess(true)->sum('amount');

        return max(0, (int) $order->total - $captured);
    }

    /**
     * Whether a payment can be recorded against the order.
     */
    public static function canRun(Order $order): bool
    {
        /** @var Order $order */
        return ! $order->isCancelled() && self::outstandingBalance($order) > 0;
    }
}
