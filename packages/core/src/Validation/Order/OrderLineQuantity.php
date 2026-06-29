<?php

namespace Lunar\Core\Validation\Order;

use Lunar\Core\Exceptions\FulfilmentException;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Validation\Fulfilment\FulfilmentQuantity;

/**
 * Protects the section A quantity invariant from the order-line side: an order
 * line's quantity may not be reduced below the total already covered by the
 * order's (non-cancelled) fulfilments. Reducing down to that fulfilled floor
 * is allowed.
 */
class OrderLineQuantity
{
    public function __construct(
        protected FulfilmentQuantity $fulfilmentQuantity,
    ) {}

    /**
     * @throws FulfilmentException
     */
    public function validate(OrderLine $orderLine, int $quantity): void
    {
        $order = $orderLine->order;

        if (! $order) {
            return;
        }

        // Acquire the same order-line row lock the fulfilment writes take, so
        // an in-flight fulfilment transaction commits before we read the
        // covered total — a reduce can't slip under a concurrent create.
        $orderLine->newQuery()->whereKey($orderLine->getKey())->lockForUpdate()->value('id');

        $covered = $this->fulfilmentQuantity->coveredQuantity($order, $orderLine->id);

        if ($quantity < $covered) {
            throw new FulfilmentException(
                __('lunar::exceptions.order_line_below_fulfilled', [
                    'fulfilled' => $covered,
                    'requested' => $quantity,
                ])
            );
        }
    }
}
