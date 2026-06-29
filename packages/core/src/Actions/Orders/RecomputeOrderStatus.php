<?php

namespace Lunar\Core\Actions\Orders;

use Lunar\Core\Contracts\Actions\Orders\RecomputesOrderStatus;
use Lunar\Core\Contracts\Actions\Orders\ResolvesFulfilmentStatus;
use Lunar\Core\Contracts\Actions\Orders\ResolvesPaymentStatus;
use Lunar\Core\Events\Orders\OrderFulfilmentStatusUpdated;
use Lunar\Core\Events\Orders\OrderPaymentStatusUpdated;
use Lunar\Core\Models\Order;

/**
 * Recompute the derived `payment_status` / `fulfilment_status` columns from
 * the transaction ledger and fulfilment rollup, firing the corresponding
 * `OrderPaymentStatusUpdated` / `OrderFulfilmentStatusUpdated` events when a
 * value actually changes.
 *
 * The rollups are written with `saveQuietly()` so they never loop back through
 * the order observer. There is no derived headline status (spec 0022) — an
 * order's lifecycle is read directly from these two rollups plus its open /
 * closed (`closed_at`) archive state.
 */
class RecomputeOrderStatus implements RecomputesOrderStatus
{
    public function __construct(
        protected ResolvesPaymentStatus $resolvePaymentStatus,
        protected ResolvesFulfilmentStatus $resolveFulfilmentStatus,
    ) {}

    public function execute(Order $order, bool $notify = true): Order
    {
        /** @var Order $order */
        $previousPayment = $order->payment_status;
        $previousFulfilment = $order->fulfilment_status;

        $paymentClass = $this->resolvePaymentStatus->execute($order);
        $fulfilmentClass = $this->resolveFulfilmentStatus->execute($order);

        $order->payment_status = new $paymentClass($order);
        $order->fulfilment_status = new $fulfilmentClass($order);

        // Quiet write so this never loops back through the order observer.
        $order->saveQuietly();

        if ($previousPayment::class !== $paymentClass) {
            OrderPaymentStatusUpdated::dispatch($order, $previousPayment, $order->payment_status);
        }

        if ($previousFulfilment::class !== $fulfilmentClass) {
            OrderFulfilmentStatusUpdated::dispatch($order, $previousFulfilment, $order->fulfilment_status, $notify);
        }

        return $order;
    }
}
