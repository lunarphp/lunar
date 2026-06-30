<?php

namespace Lunar\Core\Actions\Orders;

use Lunar\Core\Contracts\Actions\Fulfilment\CancelsFulfilment;
use Lunar\Core\Contracts\Actions\Orders\CancelsOrder;
use Lunar\Core\Events\Orders\OrderCancelled;
use Lunar\Core\Exceptions\OrderActionException;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Order;

/**
 * Cancel an order that has not been fulfilled. Voids the order's un-shipped
 * (pre-ship) fulfilments, stamps `cancelled_at` / reason / note, and closes the
 * order so it leaves the open work queue. Cancellation is one-way.
 *
 * Scope: this is the *status* side only — it does not issue a refund or
 * restock inventory (those belong to the refunds / inventory specs). The
 * `OrderCancelled` event (carrying the `notify` flag) is the seam a consumer
 * hooks to email the customer.
 */
class CancelOrder implements CancelsOrder
{
    public function __construct(
        protected CancelsFulfilment $cancelFulfilment,
    ) {}

    public function execute(Order $order, ?string $reason = null, ?string $note = null, bool $notify = true): Order
    {
        /** @var Order $order */
        if (! self::canRun($order)) {
            throw new OrderActionException(
                __('lunar::exceptions.order_not_cancellable')
            );
        }

        return DB::transaction(function () use ($order, $reason, $note, $notify) {
            // Void any un-shipped fulfilments — nothing has gone out, so they are
            // cancelled rather than left dangling.
            $order->fulfilments()
                ->whereIn('state', ['pending', 'in-progress'])
                ->get()
                ->each(fn ($fulfilment) => $this->cancelFulfilment->execute($fulfilment));

            $order->forceFill([
                'cancelled_at' => now(),
                'cancel_reason' => $reason,
                'cancel_note' => $note,
                'closed_at' => $order->closed_at ?? now(),
            ])->saveQuietly();

            activity()
                ->causedBy(auth()->user())
                ->performedOn($order)
                ->event('order-cancelled')
                ->withProperties(['reason' => $reason, 'note' => $note])
                ->log('order-cancelled');

            OrderCancelled::dispatch($order, $notify);

            return $order;
        });
    }

    /**
     * Whether the order can be cancelled — not already cancelled and nothing
     * has shipped (or returned).
     */
    public static function canRun(Order $order): bool
    {
        /** @var Order $order */
        return ! $order->isCancelled()
            && $order->fulfilments()->whereIn('state', ['shipped', 'returned'])->doesntExist();
    }
}
