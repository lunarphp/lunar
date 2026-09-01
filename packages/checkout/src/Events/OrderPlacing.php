<?php

namespace Lunar\Checkout\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Lunar\Checkout\Models\CheckoutSession;
use Lunar\Core\Models\Order;

/**
 * The order has resolved and complete() is about to decide whether to stamp
 * it placed (spec 0001 §I as amended by spec 0011 §F). On the synchronous
 * path $order is genuinely pre-placement; on the webhook-first path it was
 * already placed by the gateway during authorize(), so "about to be placed"
 * only holds for the branch that still needs the stamp — this is still the
 * one point both paths converge, inside the transaction and cart-row lock.
 *
 * This is where a host projects what its elements captured onto the order. It
 * fires exactly once per order.
 *
 * $order is a clone taken at dispatch, detached from the instance complete()
 * goes on to mutate (and, on the sync path, save()) immediately after. A
 * listener's writes to it are NOT persisted automatically — call
 * `$event->order->update([...])` (or ->save()) explicitly; Eloquent resolves
 * by primary key, so this always touches the correct row regardless of which
 * clone performs it. Any attribute change left unsaved is discarded and never
 * appears on the order complete() returns. The clone is shallow (Model
 * defines no __clone), so already-loaded relations remain shared with the
 * original instance — the isolation covers the order's own attributes only.
 *
 * The flip side of that isolation: the order instance complete() returns, and
 * the one OrderPlaced carries synchronously, do NOT reflect a listener's
 * writes. Those are on the row, not on that instance. A consumer that needs
 * them must re-fetch (`$order->fresh()`, or a fresh query by id). Consumers
 * using SerializesModels get it for free, since they reload the model by key
 * when they run — which is why the host app's order mailables see projected
 * values without doing anything special.
 *
 * The package deliberately does not project element data itself: what a
 * captured value means to a merchant's downstream systems is theirs to decide.
 */
class OrderPlacing
{
    use Dispatchable;

    public function __construct(
        public Order $order,
        public CheckoutSession $session,
    ) {}
}
