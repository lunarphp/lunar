# 0064 — Panel order settlement banner and refund composer

- Status: draft
- Author: Glenn Jacobs
- Created: 2026-08-27
- TODO item: Panel settlement banner + line-driven refund composer (spec 0064)

## Problem

The design prototype (`lunar-v2-ui`) carries two money behaviours the panel's order
view lacks:

1. **Nothing alerts the admin when the ledger diverges from the order total.** The
   prototype surfaces a net-balance alert (on its return detail: "Outstanding —
   customer owes the difference" with *Mark as collected*, or "Refund due — refund
   the difference back to the customer" with *Refund balance*). The concept
   generalises to the whole order, and core already computes the inputs —
   `ResolvePaymentStatus` compares summed captures/refunds against `order.total` and
   emits `PartiallyPaid` — but the panel renders only a status chip. Worse, an
   **over-settled** order is invisible: `captured > total` with no refunds resolves
   to plain `Paid`, so an admin has no signal that money should go back. Divergence
   is not hypothetical — order totals can change after payment (shipping/address
   corrections, future order editing), and captures can legitimately under- or
   over-shoot the total.

2. **Refunds are a bare amount.** The panel's refund dialog pre-fills
   `availableToRefund` and lets the admin type a figure. The prototype's refund flow
   (`OrderLineActionDialog`) is line-driven: a quantity stepper per line, an
   optional full-or-none shipping row, a live "Refunding £54.00 across 2 items +
   shipping" footer. Beyond ergonomics, the amount-only flow records nothing about
   *what* was refunded — the transactions table and timeline show a figure with no
   story.

Spec [[0028-line-item-refunds]] (draft) already designs the core side of per-line
refunds properly: a `refund_lines` table, `order_lines.refunded_quantity`, a
`RefundRequest` value object, per-line over-refund guards, and a dedicated Filament
refund page. This spec deliberately does not re-design any of that — it covers the
**panel presentation layer**: the settlement banner (independent of 0028) and the
panel's line-driven refund UX (which should compose with 0028 rather than compete
with it).

## Proposal

### Slice 1 — settlement banner

`OrderShowController` gains a `settlement` prop computed from the existing ledger
sums (all integer minor units, same source as `ResolvePaymentStatus`):

```
settlement: {
  status: 'balanced' | 'outstanding' | 'refund_due',
  captured: string|null,       // formatted, e.g. "£120.00"
  refunded: string|null,
  total: string,
  variance: string|null,       // formatted absolute divergence
  varianceMinor: int,          // |settled − total|, for pre-filling dialogs
}
```

Where `settled = captured − refunded` and:

- `refund_due` when `settled > total` — money held exceeds what the order is worth.
- `outstanding` when `captured > 0 && settled < total && !cancelled` — the customer
  has paid something but not everything. Orders with **zero captures** (pending /
  authorized) stay `balanced`: awaiting first capture is the normal happy path and
  the existing payment-status chip plus the Capture action already cover it. A
  cancelled order likewise never shows `outstanding` (nothing should be collected),
  but `refund_due` still fires on a cancelled order holding money.
- `balanced` otherwise — the banner does not render.

The Vue side renders an alert strip in the main column, above the Totals section
(warn tone, consistent with the hold banner on fulfilment cards):

- **outstanding**: "£12.50 outstanding — the customer has paid less than the order
  total." Primary affordance **Take payment** opens the existing capture dialog
  pre-filled with `min(varianceMinor, remaining intent amount)` when
  `actions.can_capture`; with no capturable intent the banner is informational
  (copy notes the balance must be collected outside the panel).
- **refund_due**: "£9.99 over-settled — refund the difference to the customer."
  Primary affordance **Refund** opens the refund dialog pre-filled with
  `min(varianceMinor, availableToRefund)`.

A secondary line spells out the arithmetic: "Captured £X · Refunded £Y · Order
total £Z."

Tests: Pest cases in `OrderShowTest` for each status (including the
cancelled-order and zero-capture exclusions and the over-settled `Paid` order);
a vitest spec for the banner component. New translation keys mirrored across the
16 locales.

### Slice 2 — refund composer

Replace the amount-only refund dialog with the prototype's composer shape,
submitting to the existing `panel.orders.refund` endpoint:

- One row per non-shipping order line: thumbnail, description, discounted
  tax-inclusive unit value (`line.total / quantity` — the basis 0028 leans to),
  and a quantity stepper `0..quantity`.
- A full-or-none **shipping** row when the order has an unrefunded shipping line.
- A signed **adjustment** field — the escape hatch that keeps every refund the old
  flow could express reachable (and mirrors 0028's `RefundRequest.adjustment`).
- The existing transaction select (multiple captures) and notes field stay.
- A live footer total, disabled submit at zero, client-side clamp to
  `availableToRefund`.

The request gains `lines: [{id, quantity}]`, `shipping: bool`, `adjustment` — and
the **controller recomputes the amount server-side** from the order lines (client
math is display-only), then calls `RefundOrder` exactly as today.

**Recording what was refunded.** Two options, decided by this spec's review:

- **(a) Wait for 0028 (recommended).** Land 0028's core model first
  (`refund_lines`, `refunded_quantity`, `RefundRequest`); the composer then submits
  a `RefundRequest`, steppers are bounded by *remaining* refundable quantity, and
  the transactions table / timeline render the allocation from `refund_lines`. The
  composer UI in this slice is unchanged either way — only the submit path and the
  persistence differ.
- **(b) Interim `Transaction.meta` stamp.** Persist the allocation as
  `meta['refund_allocation']` on the refund transaction and render the summary
  ("2 × Widget + shipping") from it. This needs a small core enabler — the driver
  seam returns `PaymentRefund { success, message }` with no handle on the created
  transaction, so either `PaymentRefund` gains a nullable `transaction` property
  (additive, useful regardless) or the panel fishes for the latest refund
  transaction post-hoc (fragile; rejected). Without per-line rollups the steppers
  can only bound to the line's full quantity, so repeat refunds can over-allocate a
  line even though the money guard still holds. Retired when 0028 lands.

Option (a) costs a dependency on 0028 being picked up; option (b) ships sooner but
knowingly duplicates bookkeeping 0028 replaces. The composer's UI work is common to
both, so slice 2 can be built against (a) with no waste if 0028 is scheduled next.

## Alternatives considered

- **Do nothing; rely on the payment-status chip.** Rejected — the chip neither
  quantifies the divergence nor offers the corrective action, and the over-settled
  case has no status at all (`captured > total`, no refunds → `Paid`).
- **Put the settlement logic in core (e.g. `Order::settlementVariance()`).** Not
  needed yet — it is presentation arithmetic over sums core already exposes
  (`captures()` / `refunds()`); the panel controller can own it. Promote to core if
  the Filament admin wants the same banner.
- **Client-computed refund amount.** Rejected — the server recomputes from order
  lines; the client figure is display-only.
- **Ship per-line refund accounting inside this spec.** Rejected — that is 0028's
  design, already drafted with the right shape (allocation recorded by the same
  action that moves the money).
- **A "record manual payment" action for the outstanding case** (cash / bank
  transfer tender). Out of scope — core has no manual-tender seam; the banner copy
  points at collecting externally. Candidate for its own spec.

## Migration impact

- **Database:** none in this spec. Option (b) uses the existing `meta` array cast;
  option (a) inherits 0028's migrations.
- **Public contract:** no breaking changes. `RefundOrder` is untouched. Option (b)
  adds a nullable `transaction` property to `PaymentRefund` (additive; drivers that
  do not set it keep working).
- **Panel request shape:** the refund endpoint's request grows optional `lines` /
  `shipping` / `adjustment` fields; a bare `amount` submission keeps working until
  the composer fully replaces the dialog.
- **Translations:** new `panel::orders` keys (banner copy, composer labels,
  allocation summaries) across all 16 locales.
- **Filament/admin:** untouched. The banner could later port to Filament; noted,
  not scoped.

## Open questions

- **Slice 2 path (a) vs (b)** — wait for 0028's core model or ship the interim
  meta stamp? Owner: maintainer, at spec review. Recommendation: (a), scheduling
  0028 next.
- **Refund basis for a discounted line** — `line.total / quantity` (discounted,
  tax-inclusive) is assumed here and matches 0028's leaning (Shopify behaviour).
  Confirm alongside 0028's identical open question.
- **Should `outstanding` also fire on authorized-but-uncaptured orders?** Proposed:
  no (chip + Capture action suffice); confirm at review.

## References

- Prototype: `lunar-v2-ui/src/pages/OrderReturnDetail.vue` (net-balance alert),
  `src/components/OrderLineActionDialog.vue` (line-driven refund),
  `src/pages/OrderDetail.vue` (refund flow wiring).
- Core: `Lunar\Core\Actions\Orders\ResolvePaymentStatus`,
  `Lunar\Core\Actions\Orders\RefundOrder`, `Lunar\Core\DataObjects\PaymentRefund`.
- Panel: `OrderShowController`, `OrderActionController::refund`,
  `pages/orders/Show.vue`.
- [[0028-line-item-refunds]] — the core per-line refund model this composes with.
- [[0062-panel-orders-section]], [[0063-panel-fulfilment-centric-order-view]] —
  the order view this extends.

## Implementation plan

- [ ] Slice 1 — settlement banner: `settlement` prop, alert component, dialog
      pre-fills, Pest + vitest coverage, translations.
- [ ] Slice 2 — refund composer: dialog rebuild, request shape + server-side
      amount computation, allocation recording per the resolved open question,
      transactions table / timeline allocation summaries, tests, translations.
