# 0028 — Line-item refunds

- Status: draft
- Author: Glenn Jacobs
- Created: 2026-06-09
- TODO item: Line-item refunds

## Problem

Refunds today are **amount-only**. `RefundOrder::execute(Order $order, $transactionId, $amount, $notes)` validates a flat monetary amount against the order's available-to-refund balance and dispatches it to the capture transaction's payment driver, creating a `refund`-type `Transaction`. The refund is recorded purely as money on the ledger — **nothing ties it to the order lines**.

So the system can answer "how much has been refunded?" but not "**which** lines, and how many of each, have been refunded?". A merchant refunding 2 of 5 units of one line has no record of that against the line; the order items table can't show a refunded badge or a remaining-refundable quantity; and a second partial refund can't be guarded against over-refunding a specific line. The refund UX is also a single modal (`RefundOrderAction`) — fine for a quick monetary refund, but not the line-picking flow Shopify provides.

## Proposal

Move to **line-item refunds**: the operator selects order lines and quantities to refund (plus optional shipping and an optional manual adjustment), the system computes the refund total, dispatches it to the payment driver as today, and **records the allocation against the lines**. Money remains the source of truth for payment status; the line allocation is additive bookkeeping that answers "what has been refunded".

This is the refunds work deferred by [[0022-order-fulfilments]] and [[0025-order-cancellation]] (whose cancel modal omitted the refund section pending this spec). Scope is **refunds only** — no inventory restock (deferred to the inventory spec).

### Data model

- **`refund_lines`** (new table): `id`, `transaction_id` (→ the refund `Transaction`, the money record), `order_line_id`, `quantity` (unsigned int), `amount` (int, minor units), timestamps. One row per refunded line within a refund. Shipping and manual-adjustment portions live on the refund transaction itself (no order line), so the row set explains the line-allocated portion of the refund amount.
- **`order_lines.refunded_quantity`** (new column, unsigned int, default 0): denormalised rollup of `Σ refund_lines.quantity` for the line — cheap reads for the items table and the per-line guard. (Optionally `refunded_total` for the money rollup; see open questions.)
- **`RefundLine`** model (`Lunar\Core\Models\RefundLine`) with `belongsTo` transaction + order line; `OrderLine::refundLines()` / `refunded_quantity` accessor helpers; `Transaction::refundLines()`.
- The refund **reason** and **note** are stored against the refund `Transaction` (note → existing `notes`; reason key → `meta`, or a small nullable `reason` column — see open questions).

### Core action

Evolve `RefundOrder` to take a structured request rather than a flat amount. A `RefundRequest` value object (`Lunar\Core\DataObjects\RefundRequest`) carries:

```
lines:       array<array{order_line_id: int, quantity: int}>
shipping:    int|string|float   // major-unit shipping amount to refund (optional)
adjustment:  int|string|float   // manual +/- adjustment (optional)
transactionId: int|string       // capture to refund against
reason:      ?string
note:        ?string
notify:      bool = true
```

`RefundsOrder::execute(Order $order, RefundRequest $request): PaymentRefund` (concrete `RefundOrder` implements the contract; swap by binding `RefundsOrder`, not by subclassing):

1. Resolve the capture transaction; assert it's a successful capture (existing `canRunForTransaction`).
2. Compute the refund amount = `Σ(line unit_price × quantity)` (tax-inclusive, drawn from the order line) + shipping + adjustment, in minor units via `PriceCalculatorInterface`.
3. **Guards:** amount > 0; amount ≤ `availableToRefund($order)` (existing money guard); **per-line** `quantity ≤ (order_line.quantity − refunded_quantity)` for every line. Violations throw `OrderActionException`.
4. In a transaction: dispatch to the driver (`$transaction->refund($minor, $note)`); on success persist `refund_lines` rows and bump each `order_line.refunded_quantity`; dispatch `OrderRefunded($order, $request->notify)`.
5. Return the driver's `PaymentRefund`.

`availableToRefund` / `charges` / `refunds` / `canRun` statics are retained. The amount-only path is preserved as a refund with **no line allocation** (empty `lines`, the whole amount as `adjustment`), so a quick monetary refund is still possible and just doesn't decorate any line.

### Payment status

Unchanged. `ResolvePaymentStatus` keeps deriving `Paid` / `PartiallyRefunded` / `Refunded` from the money ledger (Σ captures vs Σ refunds). Line tracking does not feed payment status — a fully line-refunded order is `Refunded` because the money says so.

### Notifications

`OrderRefunded` event + `SendOrderRefundedNotifications` listener (mirrors `OrderCancelled` / payment-status notifications): when `notify`, sends `config('lunar.orders.notifications.refunded')`. No notification ships by default (see the order-lifecycle default-notifications TODO).

### Admin — dedicated refund page

Replace the modal with a **dedicated refund page** (Shopify-style), reached by a "Refund" button on the order. A custom `OrderResource` page (`Pages\RefundOrder`) that:

- Lists the order's **refundable lines** — each with description, unit price, quantity, already-refunded quantity, and a quantity input bounded to the remaining-refundable amount.
- Lets the operator refund **shipping** (bounded to refundable shipping) and enter a **manual adjustment**.
- Shows a live **refund total** as quantities change, and the **available-to-refund** ceiling.
- Captures **reason** (select), **note** (textarea), **notify** (toggle), and the **transaction** to refund against when there's more than one capture.
- On submit, builds a `RefundRequest` and calls `RefundsOrder::execute`, surfacing driver failure as a notification (current error handling preserved).

The order **items table** gains a refunded indicator per line (e.g. "2 of 5 refunded") sourced from `refunded_quantity`. The `Refund` activity-log renderer is extended to describe the line allocation when present.

## Alternatives considered

- **Keep amount-only refunds, add a separate `refunded_quantity` field operators set by hand.** Rejected — decouples the recorded quantity from the actual money refunded; they'd drift. Allocation must be computed from the same action that moves the money.
- **A dedicated `Refund` header model** (separate from `Transaction`). Rejected — the `refund` `Transaction` already *is* the money record (amount, reference, driver, success, note); a parallel header duplicates it. The `refund_lines` table hangs off the transaction, keeping one source of truth for the ledger and payment status.
- **Couple refunds to returns** ([[0022-order-fulfilments]] `ReturnFulfilment`). Rejected for now — a return *may* trigger a refund, but they're distinct events; auto-refunding on return is a later enhancement once both exist.
- **Keep the modal.** Rejected — line picking with live totals needs the room; the user asked for a dedicated page.

## Migration impact

- **Database** (baseline editable, v2 pre-release): new `refund_lines` table; new `order_lines.refunded_quantity` column. No change to `transactions`.
- **Breaking public-contract change:** `RefundsOrder::execute` signature changes from `(order, transactionId, amount, notes)` to `(order, RefundRequest)`. Requires a **Rector rule in `lunarphp/upgrade`** to migrate callers (wrap the old args in a `RefundRequest`). The amount-only behaviour is still reachable via the request's `adjustment`. Flag in upgrade notes.
- **Additive surface:** `DataObjects\RefundRequest`, `Models\RefundLine`, `Events\Orders\OrderRefunded`, `Listeners\SendOrderRefundedNotifications`, `OrderResource\Pages\RefundOrder`, `config('lunar.orders.refund_reasons')`. The bridge `RefundOrderAction` modal is removed in favour of the page (a behaviour change worth noting; a thin deprecated shim may forward to the page route).
- **Translations (16 locales):** refund page labels, reason list, per-line "refunded" copy, notification strings, new exception messages — `lunar-filament::actions.orders.refund.*` extended, `lunar::orders.refund_reasons.*`.
- **Filament/admin:** new refund page registered on `OrderResource`; items table column for refunded quantity; activity renderer update.

## Open questions

- **`refunded_total` on `order_lines`?** Quantity covers "which lines refunded"; a money rollup per line is only needed if we show per-line refunded amounts. Add if the items table wants it, else derive from `refund_lines`.
- **Reason storage** — `meta` key on the refund transaction vs a dedicated nullable `reason` column. A column is cleaner to query/report on; leaning column.
- **Tax handling on partial-quantity refunds** — refund `unit_price × qty` tax-inclusive is the simple model; do we ever need to refund tax separately (e.g. tax-only adjustment)? The `adjustment` field can absorb it for now.
- **Over-refund on discounted lines** — line `unit_price` vs effective per-unit `total` after `discount_total`. Confirm which basis the suggested amount uses (Shopify refunds the discounted unit price). Likely the line's discounted per-unit total.
- **Restock** — deferred to the inventory spec; the refund page will grow a restock toggle then (as the cancel modal will).

## References

- Current implementation: `Lunar\Core\Actions\Orders\RefundOrder`, `Lunar\Filament\Actions\Orders\RefundOrderAction`, `Lunar\Core\Actions\Orders\ResolvePaymentStatus`, `Lunar\Core\Models\Transaction`.
- [[0022-order-fulfilments]] — order lifecycle and the `ReturnFulfilment` action refunds may later pair with.
- [[0025-order-cancellation]] — deferred its refund section to this spec.
