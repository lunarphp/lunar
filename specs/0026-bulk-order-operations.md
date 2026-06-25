# 0026 — Bulk order operations

- Status: draft
- Author: Glenn Jacobs
- Created: 2026-06-09
- TODO item: Bulk order operations

## Problem

After [[0022-order-fulfilments]] a parcel moves through a guarded per-parcel state machine and an order carries derived `payment_status` / `fulfilment_status` rollups plus an open/closed (`closed_at`) archive. Today every status change is per-order, one parcel at a time, through a modal. A store shipping 50 orders a day has no way to action a batch.

Shopify offers bulk actions on the orders list — **Mark as fulfilled**, **Capture payment**, **Archive**, **Cancel** — but the naive reading of "bulk set status = Shipped" does not fit our model:

- An order may have **multiple fulfilments** (split-down across locations). Which parcel(s) does "ship" mean?
- A selected order may contain parcels that **can't transition** — already `shipped`, terminal `cancelled`, or `on hold` (`ShipFulfilment::canRun()` is false). A blind `transitionTo()` would throw.
- Mixed selections are the norm — some orders unfulfilled, some partly shipped, some already closed.

We need bulk actions that handle a heterogeneous selection gracefully rather than failing on the first parcel that can't move.

## Proposal

Model each bulk action as a **goal-oriented operation, not a transition**. Shopify's "Mark as fulfilled" does not mean "transition this fulfilment to Shipped" — it means "bring this order to fully-fulfilled *if it can be*". Orders already there, or that can't get there, are silently skipped; the user gets a summary toast ("23 of 25 orders fulfilled"). The key property: **the operation is idempotent and self-scoping**, so a mixed selection never errors.

We already have the predicate this needs — each fulfilment action exposes `canRun()`. The bulk layer is therefore: **iterate selection → filter targets by `canRun()` → execute → tally**. Awkward cases resolve by being filtered out, not by throwing.

### Actions

Bulk Filament table actions on `OrderTable` (the orders list), each operating on the selected `Order` records:

- **Mark as shipped** — for each order, ship every parcel where `ShipFulfilment::canRun()` is true (skips already-shipped, cancelled, returned and on-hold parcels). Ships with **blank tracking** (mirrors Shopify; tracking is optional and added later per order via the existing add-tracking action).
- **Mark as in progress** — for each order, advance every `pending` parcel that can move to `in-progress`.
- **Close** / **Reopen** — bulk `closed_at` toggle via `ClosesOrder` / `ReopensOrder`, each gated by its own `canRun()`.
- **Cancel** — bulk `CancelOrder` for the orders where `CancelOrder::canRun()` is true (nothing shipped/returned); reason + notify chosen once in the modal and applied to all. Orders that can't be cancelled are skipped, not errored.

### Mechanics

- A bulk action never calls a guarded transition on a parcel that fails `canRun()`; it filters first. An order with **no** actionable parcels (or that fails the order-level guard) is counted as **skipped**.
- Per-order work runs in that order's own transaction (consistent with the single-order actions), so one bad order doesn't roll back the whole batch.
- Result is summarised: **"{processed} of {selected} orders updated"**, with skipped count surfaced. Use Filament notifications for the tally.
- Large selections should dispatch the per-order work to a **queued job** (Filament bulk-action chunking) rather than running synchronously in the request; the tally notification fires on completion. (Threshold/queue behaviour — see open questions.)

### Activity log

Each per-order operation logs its normal dedicated activity (`fulfilment-update`, `order-closed`, `order-cancelled`, …) exactly as the single-order path does — the timeline is identical whether a change came from a bulk action or a per-order modal. No new "bulk" event type.

## Alternatives considered

- **Bulk "set state = X" applied blindly.** Rejected — throws on the first non-transitionable parcel, and gives no answer for multi-parcel orders. The whole point of mirroring Shopify is that the operation scopes its own targets.
- **A single generic "bulk transition" action** parameterised by target state. Rejected — hides the per-action `canRun()` semantics and the "ship all fulfillable parcels" goal behind a state name; the named operations read better and map to what staff actually want to do.
- **Synchronous-only.** Acceptable for small selections but a 500-order batch would time out; queueing is the safe default for large sets.

## Migration impact

- No database migrations — reuses existing columns, actions and guards.
- Additive public surface only: new Filament bulk actions in the bridge/admin layer. No change to core action contracts (they already expose `execute()` + `canRun()`). No breaking change.
- Translations (16 locales): bulk-action labels, modal copy and the result-tally notification under `lunar-filament::actions.orders.*`.
- Filament / admin: new bulk actions registered on `OrderTable`; respects the existing per-order modals' field set where a bulk modal collects shared input (e.g. cancel reason/notify).

## Open questions

- **Sync vs queue threshold** — at what selection size do we push to a job? A fixed cap, or always queue? Needs a default that keeps small batches snappy.
- **Bulk ship with tracking** — Shopify backfills tracking later. Do we ever want a bulk modal that applies one carrier/service to all shipped parcels, or is blank-then-backfill enough for v1?
- **Partial-fulfilment semantics** — "mark as shipped" ships *all* fulfillable parcels of an order. Confirm there's no desire for a "ship only the first/oldest parcel" variant.
- **Capture payment bulk action** — out of scope here (payment/refund subsystem), but the same goal-oriented pattern would apply; flag for the refunds spec.

## References

- [[0022-order-fulfilments]] — per-parcel lifecycle and `canRun()` predicates this builds on.
- [[0025-order-cancellation]] — `CancelOrder` action and guard reused by the bulk cancel.
