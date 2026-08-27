<?php

namespace Lunar\Core\Actions\Orders;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Lunar\Core\Contracts\Actions\Orders\RefundsOrder;
use Lunar\Core\DataObjects\PaymentRefund;
use Lunar\Core\DataObjects\RefundRequest;
use Lunar\Core\Events\Orders\OrderRefunded;
use Lunar\Core\Exceptions\OrderActionException;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Models\RefundLine;
use Lunar\Core\Models\Transaction;
use Lunar\Core\Pricing\PriceCalculatorInterface;

/**
 * Issue a refund against an existing capture transaction on an order.
 *
 * Validates the requested lines/shipping/adjustment against the order's
 * available-to-refund total and each line's remaining refundable quantity,
 * dispatches the refund through the underlying transaction's payment driver,
 * and — when the driver hands back the refund transaction it created —
 * records the line-level allocation so "what was refunded" is answerable
 * from the ledger, not just "how much".
 */
class RefundOrder implements RefundsOrder
{
    public function __construct(
        protected PriceCalculatorInterface $priceCalculator,
    ) {}

    public function execute(Order $order, RefundRequest $request): PaymentRefund
    {
        /** @var Order $order */
        /** @var Transaction $transaction */
        $transaction = $order->transactions()->whereKey($request->transactionId)->firstOrFail();

        if (! $this->canRunForTransaction($transaction)) {
            throw new OrderActionException('Transaction is not a successful capture and cannot be refunded.');
        }

        $lineAllocations = $this->resolveLineAllocations($order, $request->lines);

        $minorAmount = array_sum(array_column($lineAllocations, 'amount'))
            + $this->priceCalculator->toMinor($request->shipping, $order->currency)
            + $this->priceCalculator->toMinor($request->adjustment, $order->currency);

        if ($minorAmount <= 0) {
            throw new OrderActionException('Refund amount must be greater than zero.');
        }

        $available = self::availableToRefund($order);

        if ($minorAmount > $available) {
            throw new OrderActionException('Refund amount exceeds the available amount on this order.');
        }

        $refund = $transaction->refund($minorAmount, $request->notes);

        if ($refund->success) {
            $this->recordLineAllocations($refund, $lineAllocations);

            event(new OrderRefunded($order, $request->notify));
        }

        return $refund;
    }

    /**
     * Resolve the requested lines against the order, guarding each against
     * its own remaining refundable quantity, and price the allocation from
     * the line's discounted, tax-inclusive per-unit total.
     *
     * @param  array<int, array{order_line_id: int, quantity: int}>  $lines
     * @return array<int, array{order_line_id: int, quantity: int, amount: int}>
     */
    protected function resolveLineAllocations(Order $order, array $lines): array
    {
        $allocations = [];

        foreach ($lines as $line) {
            $quantity = (int) ($line['quantity'] ?? 0);

            if ($quantity <= 0) {
                continue;
            }

            /** @var ?OrderLine $orderLine */
            $orderLine = $order->lines()->whereKey($line['order_line_id'])->first();

            if (! $orderLine) {
                throw new OrderActionException("Order line #{$line['order_line_id']} does not belong to this order.");
            }

            $remaining = $orderLine->refundableQuantity();

            if ($quantity > $remaining) {
                throw new OrderActionException("Refund quantity for order line #{$orderLine->id} exceeds the remaining refundable quantity ({$remaining}).");
            }

            $unitAmount = (int) round($orderLine->total / $orderLine->quantity);

            $allocations[] = [
                'order_line_id' => $orderLine->id,
                'quantity' => $quantity,
                'amount' => $unitAmount * $quantity,
            ];
        }

        return $allocations;
    }

    /**
     * Persist the line-level allocation and bump each line's
     * refunded_quantity rollup. Best-effort: a driver that doesn't hand back
     * the refund transaction it created (see {@see PaymentRefund}) means the
     * money still refunds correctly, it just isn't attributable to lines.
     *
     * @param  array<int, array{order_line_id: int, quantity: int, amount: int}>  $allocations
     */
    protected function recordLineAllocations(PaymentRefund $refund, array $allocations): void
    {
        if (! $allocations || ! $refund->transaction) {
            return;
        }

        DB::transaction(function () use ($refund, $allocations) {
            foreach ($allocations as $allocation) {
                RefundLine::create([
                    'transaction_id' => $refund->transaction->id,
                    'order_line_id' => $allocation['order_line_id'],
                    'quantity' => $allocation['quantity'],
                    'amount' => $allocation['amount'],
                ]);

                OrderLine::whereKey($allocation['order_line_id'])->increment('refunded_quantity', $allocation['quantity']);
            }
        });
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
