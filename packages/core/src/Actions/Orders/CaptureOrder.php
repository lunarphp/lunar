<?php

namespace Lunar\Core\Actions\Orders;

use Illuminate\Database\Eloquent\Collection;
use Lunar\Core\Contracts\Actions\Orders\CapturesOrder;
use Lunar\Core\DataObjects\PaymentCapture;
use Lunar\Core\Exceptions\OrderActionException;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Transaction;

/**
 * Capture a pending payment intent against an order.
 *
 * Validates the requested amount, then dispatches the capture through the
 * underlying transaction's payment driver. Returns the driver's
 * `PaymentCapture` result unchanged.
 */
class CaptureOrder implements CapturesOrder
{
    /**
     * Major-unit amount (decimal) to capture. Converted to minor units using
     * the order's currency factor before being handed to the driver.
     */
    public function execute(
        Order $order,
        int|string $transactionId,
        float|int|string $amount,
    ): PaymentCapture {
        /** @var Order $order */
        /** @var Transaction $transaction */
        $transaction = $order->transactions()->whereKey($transactionId)->firstOrFail();

        if ($transaction->type !== 'intent' || ! $transaction->success) {
            throw new OrderActionException('Transaction is not a successful payment intent and cannot be captured.');
        }

        $minorAmount = (int) bcmul((string) $amount, (string) $order->currency->factor);

        if ($minorAmount <= 0) {
            throw new OrderActionException('Capture amount must be greater than zero.');
        }

        if ($minorAmount > $transaction->amount) {
            throw new OrderActionException('Capture amount exceeds the transaction amount.');
        }

        return $transaction->capture($minorAmount);
    }

    /**
     * Whether any capture is possible against the order — i.e. there is at
     * least one successful intent transaction and no successful capture yet.
     */
    public static function canRun(Order $order): bool
    {
        /** @var Order $order */
        $intents = self::intents($order);

        if ($intents->isEmpty()) {
            return false;
        }

        $captures = $order->transactions()->whereType('capture')->whereSuccess(true)->count();

        return $captures === 0;
    }

    /**
     * Successful payment-intent transactions for an order.
     *
     * @return Collection<int, Transaction>
     */
    public static function intents(Order $order): Collection
    {
        /** @var Order $order */
        /** @var Collection<int, Transaction> $intents */
        $intents = $order->transactions()->whereType('intent')->whereSuccess(true)->get();

        return $intents;
    }
}
