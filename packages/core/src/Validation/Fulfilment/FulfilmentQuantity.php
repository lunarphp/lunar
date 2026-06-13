<?php

namespace Lunar\Core\Validation\Fulfilment;

use Lunar\Core\Exceptions\FulfilmentException;
use Lunar\Core\Models\Contracts\Order as OrderContract;
use Lunar\Core\Models\FulfilmentLine;

/**
 * Protects the §A quantity invariant from the fulfilment side: the total
 * quantity covered by (non-cancelled) fulfilments for a given order line may
 * never exceed that line's quantity.
 */
class FulfilmentQuantity
{
    /**
     * Assert that covering the given quantities is within the outstanding
     * quantity of each order line.
     *
     * The order-line row is read with `lockForUpdate()` — it acts as the mutex
     * for the line's coverage, so concurrent fulfilment writes validating the
     * same line serialise rather than both passing a stale covered total. The
     * lock only holds if the caller wraps validate-plus-write in a transaction
     * (the fulfilment actions do).
     *
     * @param  array<int|string, int>  $lines  [order_line_id => quantity]
     * @param  int|null  $ignoreFulfilmentId  exclude this fulfilment's lines
     *                                        from the already-covered total
     *                                        (used when re-validating a split)
     *
     * @throws FulfilmentException
     */
    public function validate(OrderContract $order, array $lines, ?int $ignoreFulfilmentId = null): void
    {
        foreach ($lines as $orderLineId => $quantity) {
            if ($quantity < 1) {
                throw new FulfilmentException(
                    __('lunar::exceptions.fulfilment_quantity_minimum')
                );
            }

            $orderLine = $order->lines()->whereKey($orderLineId)->lockForUpdate()->first();

            if (! $orderLine) {
                throw new FulfilmentException(
                    __('lunar::exceptions.fulfilment_order_line_not_found', [
                        'id' => $orderLineId,
                    ])
                );
            }

            if (! $orderLine->requires_fulfilment) {
                throw new FulfilmentException(
                    __('lunar::exceptions.fulfilment_line_not_fulfillable', [
                        'line' => $orderLineId,
                    ])
                );
            }

            $alreadyCovered = $this->coveredQuantity($order, (int) $orderLineId, $ignoreFulfilmentId);

            if (($alreadyCovered + $quantity) > $orderLine->quantity) {
                throw new FulfilmentException(
                    __('lunar::exceptions.fulfilment_quantity_exceeded', [
                        'line' => $orderLineId,
                        'available' => max(0, $orderLine->quantity - $alreadyCovered),
                        'requested' => $quantity,
                    ])
                );
            }
        }
    }

    /**
     * Quantity of an order line already covered by the order's non-cancelled
     * fulfilments.
     */
    public function coveredQuantity(OrderContract $order, int $orderLineId, ?int $ignoreFulfilmentId = null): int
    {
        $fulfilmentIds = $order->fulfilments()
            ->where('state', '!=', 'cancelled')
            ->when($ignoreFulfilmentId, fn ($query) => $query->whereKeyNot($ignoreFulfilmentId))
            ->pluck('id');

        return (int) FulfilmentLine::query()
            ->whereIn('fulfilment_id', $fulfilmentIds)
            ->where('order_line_id', $orderLineId)
            ->sum('quantity');
    }
}
