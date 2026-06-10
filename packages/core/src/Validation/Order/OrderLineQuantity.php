<?php

namespace Lunar\Core\Validation\Order;

use Lunar\Core\Exceptions\FulfilmentException;
use Lunar\Core\Models\Contracts\OrderLine as OrderLineContract;
use Lunar\Core\Validation\Fulfilment\FulfilmentQuantity;

/**
 * Protects the §A quantity invariant from the order-line side: an order
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
    public function validate(OrderLineContract $orderLine, int $quantity): void
    {
        $order = $orderLine->order;

        if (! $order) {
            return;
        }

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
