<?php

namespace Lunar\Core\Listeners;

use Lunar\Core\Contracts\Actions\Orders\ClosesOrder;
use Lunar\Core\Contracts\OrderSettings;
use Lunar\Core\Events\Orders\OrderFulfilmentStatusUpdated;
use Lunar\Core\Events\Orders\OrderPaymentStatusUpdated;
use Lunar\Core\States\Order\Fulfilment\Fulfilled;
use Lunar\Core\States\Order\Payment\Paid;

/**
 * Optionally close (archive) an order once it is fully paid AND fully
 * fulfilled, so a settled order drops out of the open work queue without a
 * manual click. Gated on the OrderSettings auto-close preference (off by
 * default).
 *
 * Listens to both status-changed events `RecomputeOrderStatus` fires; both
 * rollups are written before either event dispatches, so whichever fires sees
 * the final pair. Close-only — a later return/refund never reopens. Idempotent
 * via `CloseOrder` (a closed/cancelled order is skipped by the open guard).
 */
class CloseSettledOrder
{
    public function __construct(
        protected ClosesOrder $closeOrder,
        protected OrderSettings $settings,
    ) {}

    public function handle(OrderPaymentStatusUpdated|OrderFulfilmentStatusUpdated $event): void
    {
        $order = $event->order;

        if (! $this->settings->autoClosesSettledOrders($order)) {
            return;
        }

        if ($order->isOpen()
            && $order->payment_status instanceof Paid
            && $order->fulfilment_status instanceof Fulfilled) {
            $this->closeOrder->execute($order);
        }
    }
}
