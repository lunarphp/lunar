# 0069 — Panel order screen improvements

- Status: draft
- Author: Glenn Jacobs
- Created: 2026-08-27
- TODO item: Panel order screen improvements — admin-workflow gaps found reviewing 0066-0068 (spec 0069)

## Problem

With [[0066-panel-orders-section]], [[0067-panel-fulfilment-centric-order-view]] and
[[0068-panel-settlement-banner-refund-composer]] landed, the panel's order screens
cover the core merchant loop: find an order, read its state, fulfil it, take or
return money. A review of both screens against the design prototype
(`lunar-v2-ui`) and the Filament admin surfaced a set of gaps that are neither
already-specced work (bulk operations [[0026-bulk-order-operations]], print
templates [[0027-order-print-templates]]) nor deliberately-deferred domains
(returns/RMA, invoices and credit notes).

They are collected here as one improvements pass rather than seven loose issues,
because each is small on its own and they share the same two files
(`OrderShowController`, `pages/orders/Show.vue`). Each item below is
independently shippable — this spec is a menu, not a package deal.

None of these block the current work; they are the difference between "an admin
can operate an order" and "an admin can operate an order without leaving the
screen or guessing".

## Proposal

Ordered by value-to-effort as judged at review. Items 1-3 are the substantive
ones; 4-7 are small.

### 1. Stock visibility on fulfilment cards

A fulfilment card tells the admin what to pick but never whether it is in stock.
Core has the inventory model ([[0038-inventory-fundamentals]]) and the hold-reason
vocabulary already ships `out-of-stock`, so today an admin holds a fulfilment for
a stock problem the screen never showed them. This was explicitly deferred during
0063's Q&A ("stock levels deferred"); the fulfilment view is now real enough to
revisit it.

Proposed: each fulfilment line row gains the purchasable's available stock at the
fulfilment's location, shown only when it is below the allocated quantity (a quiet
warn-tone hint — "2 of 3 available"), so the common in-stock case adds no noise.
Needs a decision on which stock figure is authoritative for a location-scoped
fulfilment; see open questions.

### 2. Activity feed pagination

`OrderShowController` hard-limits the deferred activity feed to the latest 25
entries with no way to reach older ones. On a long-lived order (splits, holds,
address corrections, refunds) the earliest history is simply unreachable — and
since 0063 added address-change logging, the audit trail is the first thing to be
truncated.

Proposed: a "Load older activity" affordance backed by a cursor-paginated
endpoint, keeping the existing deferred first page.

### 3. Money movements on the activity timeline

Captures and refunds appear only in the Transactions table, so the timeline reads
as a partial story: it shows that a shipping address was corrected but not that
GBP 40 was refunded an hour later. "What happened to this order, in order" is not
answerable from one place.

Proposed: log capture/refund/void as order activity events (core-side, so the
Filament admin benefits too) and render them in `TimelineActivity` alongside the
existing events. The Transactions table stays the ledger; the timeline gains the
narrative. Note this is a core change, not panel-only — it needs its own review of
whether payment events belong in the activity log at all, or whether the panel
should merge two sources at render time (see open questions).

### 4. Copy-to-clipboard on address cards

The prototype has a copy button on both address cards. Warehouse staff retype
addresses into carrier systems constantly. Copies the formatted multi-line
address; a small, high-frequency win.

### 5. Contact email on address cards

`contact_email` is already in the address payload but only `contact_phone`
renders. The Customer side card shows an email, but for a guest order with
different billing and shipping contacts that is the wrong one. Render the
address's own email next to its phone.

### 6. Shipping-refund tracking

0064 ships shipping refunds as full-or-none with no record that shipping has
already been refunded — only the order's overall available-to-refund balance
stops a second attempt. Proposed: track the refunded shipping amount (either a
`refund_lines` row with a null `order_line_id`, or a rollup on the order) and hide
or disable the shipping row in the composer once it is spent. Needs a small core
decision; see open questions.

### 7. Settlement filter on the orders index

0064 added the settlement concept but only on the show screen, so "show me
everything over- or under-settled" — exactly the queue an accounts person wants —
is not answerable from the list. During 0063 a per-store shipping-option filter
was declined as too opinionated for core; a settlement filter is different in
kind, being derived from the ledger rather than a store's process. Proposed: a
lifecycle-style filter with `outstanding` / `refund_due` options.

Note this needs the settlement status to be expressible in SQL. Today it is
computed per-row in PHP from already-loaded sums, so this item likely forces the
arithmetic into a query scope (and possibly into core, which 0064's alternatives
section anticipated as the trigger for promoting it).

## Out of scope — needs its own spec

**Order creation and editing.** The panel is read-and-react only: there is no way
to create an order, and no way to amend one. The prototype has a full
`OrderCreate` screen (customer picker, line builder, payment capture), and phone
and trade orders are routine for the merchants Lunar targets. This is the single
largest functional gap in the orders section.

It is deliberately *not* designed here — it is a screen, a draft-order lifecycle,
a pricing/tax recalculation path and a payment-collection flow, easily larger than
everything above combined. It is recorded in this spec only so the gap is not lost.
It does not currently appear on `TODO.md` under any spec, which may be an
oversight rather than a decision.

## Alternatives considered

- **File these as issues rather than a spec.** Rejected — the repo's convention is
  spec-first, and several items (3, 6, 7) have core implications that deserve
  review before implementation rather than being discovered mid-PR.
- **One big "order screens v2" spec including order creation.** Rejected — order
  creation is a different order of magnitude and would stall the small wins behind
  a much larger design conversation.
- **Do nothing; the screens are functional.** Reasonable for items 4-5, which are
  polish. Less so for 1-3, where the missing information changes what an admin
  does (holding stock they cannot see, auditing history they cannot reach).

## Migration impact

- **Database:** none for items 2, 4, 5, 7. Item 6 needs either a `refund_lines`
  shape change (nullable `order_line_id`) or an order-level rollup column. Items 1
  and 3 depend on their open questions.
- **Public contract:** additive only, unless item 3 moves payment-event logging
  into core (new activity events; additive but a new observable surface).
- **Translations (16 locales):** new `panel::orders` keys per item.
- **Filament/admin:** item 3 benefits the Filament order page for free if logged
  core-side. Everything else is panel-only.

## Open questions

- **Item 1 — which stock figure?** A fulfilment carries a `location_id`, so the
  honest figure is location-scoped availability, but 0038's location-scoped stock
  routing is itself listed as a follow-on idea. Falling back to the global figure
  may mislead more than it helps on a multi-location store. Owner: maintainer.
- **Item 3 — activity log or render-time merge?** Writing payment events to the
  activity log makes them permanent and shared with Filament, but the transactions
  table is already the durable record; merging at render time avoids duplicating
  the ledger. Leaning render-time merge in the panel, which keeps it panel-only
  and reversible. Owner: maintainer.
- **Item 6 — where does refunded shipping live?** A `refund_lines` row with a null
  `order_line_id` keeps one allocation table but weakens the column's meaning; an
  order-level rollup is simpler but adds a second place refunds are recorded.
  Relates to [[0028-line-item-refunds]]'s closed "refunded_total" question.
- **Item 7 — does settlement move to core?** Required if the index filters on it
  in SQL, and would let Filament reuse it. 0064 deliberately kept it in the panel
  controller until a second consumer appeared; this would be that consumer.

## References

- Prototype: `lunar-v2-ui/src/pages/OrderDetail.vue`, `src/pages/OrdersList.vue`,
  `src/pages/OrderCreate.vue`.
- Panel: `OrderShowController`, `OrderIndexController`,
  `pages/orders/Show.vue`, `Support/TimelineActivity.php`.
- [[0066-panel-orders-section]], [[0067-panel-fulfilment-centric-order-view]],
  [[0068-panel-settlement-banner-refund-composer]] — the work this reviews.
- [[0038-inventory-fundamentals]] — the stock model item 1 would read from.
- [[0028-line-item-refunds]] — item 6 extends its allocation model.

## Implementation plan

Each slice is independently shippable; order reflects value-to-effort, not
dependency (there are none between them).

- [ ] Slice 1 — small wins: address copy-to-clipboard, contact email on address
      cards (items 4, 5).
- [ ] Slice 2 — activity feed pagination (item 2).
- [ ] Slice 3 — stock visibility on fulfilment lines (item 1), once its open
      question is resolved.
- [ ] Slice 4 — money movements on the timeline (item 3), once its open question
      is resolved.
- [ ] Slice 5 — shipping-refund tracking (item 6).
- [ ] Slice 6 — settlement filter on the orders index (item 7).
