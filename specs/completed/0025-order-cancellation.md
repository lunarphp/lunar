# 0025 — Order cancellation

- Status: implemented
- Author: Glenn Jacobs
- Created: 2026-06-09
- TODO item: Order fulfilments follow-up — cancel an unfulfilled order

> **Amendment (2026-06-15):** `config('lunar.orders.cancel_reasons')` was removed
> before release — cancellation reasons now come from the `CancelReasons` manifest
> (code defaults + an override seam), ahead of a store-scoped set. See
> [[0033-reduce-config-surface]].

## Problem

After [[0022-order-fulfilments]] an order's lifecycle is its derived `payment_status` / `fulfilment_status` plus an open/closed (`closed_at`) archive. There is no way to **cancel** an order — Shopify lets an admin cancel an order that hasn't been fulfilled (with a reason, staff note and optional customer notification). We want the equivalent, scoped to the **status** side only.

## Proposal

Cancellation is modelled as a flag on the order — `cancelled_at` + `cancel_reason` + `cancel_note` — mirroring the `closed_at` pattern and Shopify's own `cancelled_at` / `cancel_reason`. It is **one-way** (no un-cancel) and deliberately covers status only: **no refund is issued and no stock is restocked** — those belong to the future refunds and inventory specs.

### Columns & model

- `orders.cancelled_at` (datetime, nullable, indexed), `cancel_reason` (string, nullable), `cancel_note` (text, nullable).
- `Order::isCancelled()`, `scopeCancelled()`, `cancelReasonLabel()` (resolves the reason key against the config list).
- `Order::lifecycleStatus(): 'cancelled'|'closed'|'open'` — **cancelled takes precedence** over the open/closed archive state. Used by the table column, order summary and global search; maps to `lunar::states.order.*`.

### `CancelOrder` action (`Contracts\Actions\Orders\CancelsOrder`)

`execute(Order $order, ?string $reason, ?string $note, bool $notify = true): Order`.

- **Guard (`canRun`):** not already cancelled, and **nothing has shipped** — no fulfilment in `shipped` / `returned`. (So unfulfilled physical orders and digital-only orders are cancellable; partially/fully shipped orders are not.)
- **Effect (one transaction):** void the order's un-shipped (`pending` / `in-progress`) parcels via `CancelsFulfilment` (terminal `Cancelled` parcel state); stamp `cancelled_at` / reason / note; **also close** the order (`closed_at`) so it leaves the open work queue; dispatch `OrderCancelled` (carrying the `notify` flag).
- An illegal cancel throws `OrderActionException` (`lunar::exceptions.order_not_cancellable`).

### Reasons & notifications

- Configurable `config('lunar.orders.cancel_reasons')` (key ⇒ label): customer, items-unavailable, fraud, declined, other.
- `OrderCancelled` event + `SendOrderCancelledNotifications` listener: when `notify` is true, sends `config('lunar.orders.notifications.cancelled')` notifications (same pattern as payment/fulfilment). No notification ships by default.

### Admin

- A danger **"Cancel order"** header action (`CancelOrderAction`) with a modal: **Reason** (select), **Staff note** (textarea), **Send notification** (toggle, default on). Visible only when `CancelOrder::canRun()`.
- The order summary, table status column and global search show **"Cancelled"** (red) via `lifecycleStatus()`.
- `ReopenOrder::canRun()` excludes cancelled orders — a cancelled order is closed but must not be reopened (one-way).
- The Shopify modal's *Refund payments* and *Restock inventory* sections are intentionally omitted until those subsystems exist.

## Alternatives considered

- **A dedicated `Cancelled` order state** (reviving the headline machine removed in 0022). Rejected: 0022 deliberately dropped the headline; a flag is consistent and avoids reintroducing the machine.
- **Reversible cancellation.** Rejected for now — Shopify treats cancel as terminal, and un-cancel needs rules for restoring voided parcels. A mistaken cancel is handled by a new order.

## Migration impact

- Baseline `..._create_orders_table.php` edited in place (v2 pre-release): add `cancelled_at`, `cancel_reason`, `cancel_note`.
- Additive public surface: `Contracts\Actions\Orders\CancelsOrder`, `Actions\Orders\CancelOrder`, `Events\Orders\OrderCancelled`, `Listeners\SendOrderCancelledNotifications`, `Filament\Actions\Orders\CancelOrderAction`. No breaking change.
- Translations (16 locales): new `lunar::exceptions.order_not_cancellable`, `lunar::states.order.cancelled`, `lunar-filament::actions.orders.cancel_order.*`; config `cancel_reasons` labels.

## Open questions

- Refund-on-cancel (original payment method / later) — deferred to the refunds spec; the cancel modal will grow a refund section then.
- Restock-on-cancel — deferred to the inventory spec.

## References

- [[0022-order-fulfilments]] — open/closed lifecycle this builds on.
