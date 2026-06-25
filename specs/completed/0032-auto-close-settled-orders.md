# 0032 — Auto-close settled orders

- Status: implemented
- Author: Glenn Jacobs
- Created: 2026-06-13
- TODO item: "Auto-close settled orders — optionally close an order once it is fully paid and fully fulfilled"

> **Amendment (2026-06-15):** `config('lunar.orders.auto_close')` was removed
> before release — the preference is now read from the `OrderSettings` seam
> (default off; bind a custom implementation to opt in or vary it per store).
> See [[0033-reduce-config-surface]].

## Problem

Spec [[0022-order-fulfilments]] replaced the headline order status with two derived rollups (`payment_status`, `fulfilment_status`) plus an **open / closed** archive: `closed_at` is the inbox-zero flag, and the order list defaults to the **Open** work queue. Closing is a deliberate `CloseOrder` action — and today it is *only* manual (the admin "Close" button, or `$order->close()`).

So an order that is fully paid and fully fulfilled — genuinely dealt with — sits in the Open queue until a human archives it. For a high-volume merchant the queue never empties on its own, which defeats the point of the open/closed split. Shopify solves this with an "automatically archive the order" setting (after the order is paid and fulfilled); Lunar's model is the same archive but has no equivalent.

## Proposal

Add an **opt-in** preference that closes an order the moment it becomes fully paid **and** fully fulfilled. It reuses the existing `CloseOrder` action and the existing status-changed events — no new lifecycle concept, no schema.

### Config

`config/orders.php` gains a documented boolean, **defaulting to `false`** so existing behaviour is unchanged and a merchant opts in (a value, not behaviour → config, per [[0016-service-layer-di]]):

```php
'auto_close' => false,
```

### Listener

A new `Lunar\Core\Listeners\CloseSettledOrder` listens to **both** `OrderPaymentStatusUpdated` and `OrderFulfilmentStatusUpdated` — the two events `RecomputeOrderStatus` already fires when a rollup changes. Both rollups are written *before* either event dispatches, so whichever fires sees the final pair. It injects the `ClosesOrder` action ([[0016-service-layer-di]]):

```php
if (! config('lunar.orders.auto_close', false)) {
    return;
}

if ($order->isOpen()
    && $order->payment_status instanceof Paid
    && $order->fulfilment_status instanceof Fulfilled) {
    $this->closeOrder->execute($order);
}
```

Registered alongside the existing notification listeners in `LunarServiceProvider::boot()`:

```php
Event::listen(OrderPaymentStatusUpdated::class, CloseSettledOrder::class);
Event::listen(OrderFulfilmentStatusUpdated::class, CloseSettledOrder::class);
```

`CloseOrder` is idempotent (closing a closed order is a no-op preserving the original `closed_at`), so both events firing in one recompute is harmless; a cancelled order is already closed, so the `isOpen()` guard skips it.

### Scope of "settled"

- `payment_status` is exactly `Paid` (not `PartiallyPaid` / `Authorized` / `PartiallyRefunded` / …).
- `fulfilment_status` is exactly `Fulfilled` (not `PartiallyFulfilled`, and not `Returned` / `PartiallyReturned`).

A partially-anything order, or one with an outstanding balance, stays open.

### Close-only — no auto-reopen

If an auto-closed order later regresses (a return → `PartiallyReturned`, a refund → `PartiallyRefunded`), it stays closed. The listener never reopens; reopening is a deliberate `reopen()` action. The return/refund is still recorded against the order and surfaced through its rollups and notifications.

### Instantly-settled orders

An order with nothing to fulfil (`fulfillableLines()` empty — every line `requires_fulfilment = false`, e.g. a pure service) resolves `Fulfilled` immediately, so with `auto_close` on it closes as soon as it is paid (or on creation for a zero-total order). This is intended and accepted; a merchant who wants such orders to stay open leaves `auto_close` off.

## Alternatives considered

- **An admin prompt / "this looks complete — close it?" nudge.** Rejected: the admin already has a one-click Close action, so a nudge is largely redundant, and it only helps a human in Filament — a config flag is server-side, so it archives identically for the storefront, the API and agents (the parity bar the rest of this work holds).
- **Default `on`.** Rejected: silently changing the archive behaviour of every consumer is surprising; opt-in matches Shopify's auto-archive default. Trivial to flip.
- **Auto-reopen on regression.** Rejected for v1: less predictable (it can fight a merchant who deliberately archived an order), and returns/refunds already have their own surfaces. Revisit if merchants ask.
- **Inline in `RecomputeOrderStatus`.** Rejected: keeps the recompute action pure (just the two rollups) and uses the same event-listener seam the notifications already use.

## Migration impact

- **Database:** none.
- **Breaking changes to the public contract surface:** none — additive only: a new `Listeners\CloseSettledOrder` and a new `config('lunar.orders.auto_close')` key that defaults to today's behaviour. Nothing to Rector.
- **Upgrade path:** none — the flag defaults off, so v1.x → v2 consumers see no change unless they opt in.
- **Translation / locale impact (16 locales):** none — no new user-facing strings; the config key is documented inline in `config/orders.php`.
- **Filament / admin impact:** none directly — the Close / Reopen actions are unchanged. With the flag on, settled orders simply drop out of the Open queue without a click.

## Open questions

None outstanding — default (off), close-only (no auto-reopen), and the instantly-settled behaviour are decided above.

## References

- [[0022-order-fulfilments]] — the open/closed (`closed_at`) archive, `CloseOrder` / `ReopenOrder`, the derived `payment_status` / `fulfilment_status` rollups, and `RecomputeOrderStatus` this hangs off.
- [[0016-service-layer-di]] — config-for-data; the listener injects its `ClosesOrder` collaborator rather than reaching for it.
