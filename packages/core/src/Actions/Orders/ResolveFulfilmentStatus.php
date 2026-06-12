<?php

namespace Lunar\Core\Actions\Orders;

use Lunar\Core\Contracts\Actions\Orders\ResolvesFulfilmentStatus;
use Lunar\Core\Models\Contracts\Order as OrderContract;
use Lunar\Core\Models\FulfilmentLine;
use Lunar\Core\Models\Order;
use Lunar\Core\States\Order\Fulfilment\Fulfilled;
use Lunar\Core\States\Order\Fulfilment\PartiallyFulfilled;
use Lunar\Core\States\Order\Fulfilment\PartiallyReturned;
use Lunar\Core\States\Order\Fulfilment\Returned;
use Lunar\Core\States\Order\Fulfilment\Unfulfilled;

/**
 * Roll the order's `Fulfilment` records up into an order-level fulfilment
 * status. Only fulfilments in `shipped` / `returned` count as dispatched.
 * Orders with no fulfillable lines resolve to `Fulfilled`.
 */
class ResolveFulfilmentStatus implements ResolvesFulfilmentStatus
{
    public function execute(OrderContract $order): string
    {
        /** @var Order $order */
        $fulfillableLineIds = $order->fulfillableLines()->pluck('id');

        // Nothing to ship — settled by definition.
        if ($fulfillableLineIds->isEmpty()) {
            return Fulfilled::class;
        }

        $totalQuantity = (int) $order->fulfillableLines()->sum('quantity');

        $shippedFulfilmentIds = $order->fulfilments()
            ->whereIn('state', ['shipped', 'returned'])
            ->pluck('id');

        $returnedFulfilmentIds = $order->fulfilments()
            ->where('state', 'returned')
            ->pluck('id');

        $shippedQuantity = (int) FulfilmentLine::query()
            ->whereIn('fulfilment_id', $shippedFulfilmentIds)
            ->whereIn('order_line_id', $fulfillableLineIds)
            ->sum('quantity');

        $returnedQuantity = (int) FulfilmentLine::query()
            ->whereIn('fulfilment_id', $returnedFulfilmentIds)
            ->whereIn('order_line_id', $fulfillableLineIds)
            ->sum('quantity');

        return match (true) {
            $returnedQuantity > 0 && $returnedQuantity >= $totalQuantity => Returned::class,
            $returnedQuantity > 0 => PartiallyReturned::class,
            $shippedQuantity === 0 => Unfulfilled::class,
            $shippedQuantity >= $totalQuantity => Fulfilled::class,
            default => PartiallyFulfilled::class,
        };
    }
}
