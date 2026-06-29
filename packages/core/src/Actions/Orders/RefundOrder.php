<?php

namespace Lunar\Core\Actions\Orders;

use Illuminate\Database\Eloquent\Collection;
use Lunar\Core\Contracts\Actions\Orders\RefundsOrder;
use Lunar\Core\DataObjects\PaymentRefund;
use Lunar\Core\Exceptions\OrderActionException;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Transaction;
use Lunar\Core\Pricing\PriceCalculatorInterface;

/**
 * Issue a refund against an existing capture transaction on an order.
 *
 * Validates the requested amount against the order's available-to-refund
 * total, then dispatches the refund through the underlying transaction's
 * payment driver. Returns the driver's `PaymentRefund` result unchanged.
 */
class RefundOrder implements RefundsOrder
{
    public function __construct(
        protected PriceCalculatorInterface $priceCalculator,
    ) {}

    /**
     * Major-unit amount (decimal) to refund. Converted to minor units using
     * the order's currency factor before being handed to the driver.
     */
    public function execute(
        Order $order,
        int|string $transactionId,
        float|int|string $amount,
        ?string $notes = null,
    ): PaymentRefund {
        /** @var Order $order */
        /** @var Transaction $transaction */
        $transaction = $order->transactions()->whereKey($transactionId)->firstOrFail();

        if (! $this->canRunForTransaction($transaction)) {
            throw new OrderActionException('Transaction is not a successful capture and cannot be refunded.');
        }

        $minorAmount = $this->priceCalculator->toMinor($amount, $order->currency);

        if ($minorAmount <= 0) {
            throw new OrderActionException('Refund amount must be greater than zero.');
        }

        $available = self::availableToRefund($order);

        if ($minorAmount > $available) {
            throw new OrderActionException('Refund amount exceeds the available amount on this order.');
        }

        return $transaction->refund($minorAmount, $notes);
    }

    /**
     * Whether any refund is possible against the given order — i.e. there is
     * at least one successful capture transaction and remaining
     * available-to-refund balance.
     */
    public static function canRun(Order $order): bool
    {
        /** @var Order $order */
        return self::charges($order)->isNotEmpty() && self::availableToRefund($order) > 0;
    }

    /**
     * Available-to-refund balance for an order, in the order's minor units.
     */
    public static function availableToRefund(Order $order): int
    {
        /** @var Order $order */
        return (int) (self::charges($order)->sum('amount') - self::refunds($order)->sum('amount'));
    }

    /**
     * Successful capture transactions for an order.
     *
     * @return Collection<int, Transaction>
     */
    public static function charges(Order $order): Collection
    {
        /** @var Order $order */
        /** @var Collection<int, Transaction> $charges */
        $charges = $order->transactions()->whereType('capture')->whereSuccess(true)->get();

        return $charges;
    }

    /**
     * Successful refund transactions for an order.
     *
     * @return Collection<int, Transaction>
     */
    public static function refunds(Order $order): Collection
    {
        /** @var Order $order */
        /** @var Collection<int, Transaction> $refunds */
        $refunds = $order->transactions()->whereType('refund')->whereSuccess(true)->get();

        return $refunds;
    }

    protected function canRunForTransaction(Transaction $transaction): bool
    {
        /** @var Transaction $transaction */
        return $transaction->type === 'capture' && $transaction->success;
    }
}
