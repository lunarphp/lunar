# 0022 — Order fulfilments, derived statuses & open/closed lifecycle

- Status: implemented
- Author: Glenn Jacobs
- Created: 2026-06-04
- TODO item: "Implement state machines, replacing soft-deletes — products & orders" (the payment/fulfilment half deferred from spec 0021)

> **Amendment — the headline `OrderState` is removed (open/closed lifecycle).** As originally written this spec kept a derived headline `Order::$status` (§D) computed from payment × fulfilment with `OnHold` / `Cancelled` / `Refunded` overrides. In review that third status proved confusing alongside the two rollups it derived from, so it was **dropped entirely**. An order now carries only the two derived rollups (`payment_status`, `fulfilment_status`) plus a Shopify-style **open/closed archive**: a nullable `closed_at` timestamp (null = open, set = closed/archived), with `Order::isOpen()/isClosed()`, `scopeOpen()/scopeClosed()`, and `CloseOrder` / `ReopenOrder` actions (`OrderClosed` / `OrderReopened` events). This **supersedes §D in full** and amends §E/§F/§G: there is no `OrderState` machine, no `OrderStateConfig`, no `computeOrderStatus()`/`overrideStates()`, and no `OrderStatusUpdated`/headline notifications. `OrderStateCategory`, `States/Order/Order/*`, and the order-status-tied actions (`MarkOrderAsComplete/Shipped`, `UpdateOrderStatus`) and Filament actions (Cancel/Hold/Resume/MarkComplete/MarkShipped/UpdateStatus) are removed. Order-level notifications are sent off `payment_status` / `fulfilment_status` changes (`SendOrderPaymentStatusNotifications` / `SendOrderFulfilmentStatusNotifications`) instead. **`States/Order/Payment/*` and `States/Order/Fulfilment/*` (the two derived rollups in §B/§C) are unaffected and remain.** The fulfilment model (§A) and operations (§G) are likewise unaffected.

> **Amendment (2026-06-15):** the hold-reason list moved off `config/fulfilment.php`
> (now removed) — reasons come from the `HoldReasons` manifest (code defaults + an
> override seam), ahead of a store-scoped set. See [[0033-reduce-config-surface]].

> **Handover from [[0021-state-machines]].** Spec 0021 deliberately shipped the order as a **single** hand-driven `OrderState` machine and deferred the payment/fulfilment decomposition to "the next spec, which can design them once with every source record on the table." This is that spec. It introduces the order-fulfilment concept, derives **payment status** from `transactions` (already on the table) and **fulfilment status** from the new `Fulfilment` records. (It originally also re-derived the headline `Order::$status`; per the amendment above that headline has since been removed in favour of the open/closed archive.) The items 0021 explicitly dropped — `States/Order/Payment/*`, `States/Order/Fulfilment/*` — are reintroduced here, now backed by real source records.

## Problem

After spec 0021, an order carries one typed `OrderState` (`awaiting-payment → in-process → shipped → complete`, plus `on-hold` / `cancelled` / `refunded`). It works, but it is **entirely hand-driven** and that creates three gaps:

- **Payment progress is invisible as state.** Every order already accrues `transactions` (`type` ∈ `intent` / `capture` / `refund`, a `success` bool, an `amount`, a `captured_at`; `2026_01_01_000053_create_transactions_table.php`). The money story — authorized, partly captured, fully paid, partly refunded, refunded, voided — is fully knowable from those rows, yet nothing rolls them up. A merchant reads transactions by hand and flips `status` to match. There is no `payment_status` to query, filter, or notify on.
- **There is no fulfilment concept at all.** Nothing in core models "what shipped, when, with what tracking." `Order::physicalLines()` exists, but there is no record that a subset of those lines went out in a parcel. So `PartiallyShipped` and `Backordered` (defined in 0021's graph) are *settable by hand but mean nothing* — a merchant can click "Partially Shipped" with zero record of what was actually sent. 0021 called this out: "a single fulfilment can't be 'partially shipped'."
- **The headline status drifts from reality.** Because `status`, the transaction ledger, and (soon) shipments are independent, they fall out of sync. An order can read `Complete` while a refund transaction sits against it, or `Shipped` with nothing dispatched. The single column cannot be both the merchant's manual lever *and* a faithful summary of payment + fulfilment, because today it is only the former.

Net effect: the money and the parcels are facts the system already has (or trivially could), but the order's headline state is a hand-maintained guess rather than a derivation from them.

## Proposal

Three coordinated pieces:

1. **`Fulfilment` (line-item level)** — a new record modelling a parcel/shipment that covers specific `order_lines` with quantities, carrying its own transition-guarded lifecycle.
2. **Derived `payment_status`** — a rollup of `transactions` into the Shopify `financial_status` vocabulary, recomputed whenever a transaction changes.
3. **Derived headline `Order::$status`** — computed from `payment_status` × `fulfilment_status`, **except** when a manual override state (`OnHold` / `Cancelled` / `Refunded`) is set, which sticks until resumed.

All state classes stay under `Lunar\Core\States\…` (the folder 0013/0021 established).

### A. Fulfilment model — line-item level

A `Fulfilment` belongs to an order and covers one-or-more `order_lines` with a quantity each (Shopify-style), so partial and split shipments are first-class.

```
fulfilments
├── id
├── order_id            FK → orders, cascadeOnDelete
├── location_id         FK → locations, required, restrictOnDelete  (the place it ships from)
├── reference           string, nullable, indexed   (optional human/carrier ref; not auto-generated)
├── state               string, indexed   (FulfilmentState $name; default 'pending')
├── notes               text, nullable
├── meta                jsonb, nullable
├── shipped_at          datetime, nullable, indexed
└── timestamps

fulfilment_trackings
├── id
├── fulfilment_id       FK → fulfilments, cascadeOnDelete
├── carrier             string, nullable  (registered carrier key — see spec 0024)
├── shipping_method     string, nullable  (carrier service / method)
├── tracking_number     string, nullable
├── tracking_url        string, nullable  (explicit override; otherwise derived from carrier + number)
├── meta                jsonb, nullable
└── timestamps

fulfilment_lines
├── id
├── fulfilment_id       FK → fulfilments, cascadeOnDelete
├── order_line_id       FK → order_lines, cascadeOnDelete
├── quantity            unsigned integer
└── timestamps
        UNIQUE (fulfilment_id, order_line_id)
```

`Fulfilment` extends `Models\Base`, implements `Contracts\Fulfilment`, uses `HasFactory` / `HasMacros` / `LogsActivity`. Relations: `order()` (BelongsTo), `location()` (BelongsTo), `lines()` (HasMany `FulfilmentLine`), `trackings()` (HasMany `FulfilmentTracking`). `FulfilmentLine` has `fulfilment()` and `orderLine()` BelongsTo. `Order` gains `fulfilments(): HasMany`.

**Tracking is one-to-many.** A parcel can carry several tracking references (a shipment split across boxes or carriers), so tracking lives in `fulfilment_trackings` rather than columns on the fulfilment. `ShipFulfilment` accepts a list of tracking entries (or a single one) and records them on ship; `AddFulfilmentTracking` (`Fulfilments::addTracking()`) appends more afterwards as carrier details arrive. A tracking row may reference a **registered shipping carrier** (`carrier` key) which supplies the service options and derives the public tracking URL from the number — see **spec 0024 (Shipping carriers)**. `tracking_url` is only stored when a carrier can't resolve one (the "custom" case).

#### Locations

A `Fulfilment` is assigned to a single **`Location`** — the warehouse or store it ships from. `Location` is a new top-level model (it will also anchor multi-location **inventory** in a later spec):

```
locations
├── id
├── name      string
├── handle    string, unique   (slugged)
├── default   boolean, indexed  (one default location)
├── meta      jsonb, nullable
└── timestamps
```

`Location` extends `Models\Base`, implements `Contracts\Location`, uses `HasDefaultRecord` (so `Location::getDefault()` mirrors `Channel`/`Currency`) / `HasFactory` / `HasMacros` / `LogsActivity`. `InstallLunar` seeds a `Default` location, the same way it seeds the default channel and currency.

`location_id` is **required** — a fulfilment always ships from a location. `CreateFulfilment` stamps it from the passed attributes, defaulting to the default location (falling back to any existing location, then to creating the `Default` one, so the column is always resolvable); `SplitFulfilment` propagates the source's location to the new parcel. A location with fulfilments cannot be deleted (`restrictOnDelete`). Crucially, **fulfilments at different locations cannot be combined** — both `MergeFulfilments` and `MoveFulfilmentLines` (§G) guard that target and sources share a `location_id`, throwing `FulfilmentException` otherwise. The admin only offers same-location parcels as merge targets.

**Quantity invariant.** The sum of `fulfilment_lines.quantity` for a given `order_line` may never exceed that line's `quantity`. Enforced by a `Validation/Fulfilment/FulfilmentQuantity` rule used by `CreateFulfilment` / `SplitFulfilment` (§G), not by a DB constraint (cross-row sum). The invariant is protected from **both** sides: a fulfilment cannot cover more than the line carries, and — symmetrically — an order line's `quantity` **cannot be reduced below the total already covered by fulfilments**. The latter is a `Validation/Order/OrderLineQuantity` rule on order-line updates, so the invariant always holds rather than only being checked at fulfilment-creation time. Reducing a line is otherwise allowed down to its fulfilled floor. Because the check is a cross-row sum in PHP, concurrent writes serialise on the **order-line row** as a mutex: every coverage-changing write (`CreateFulfilment`, `SplitFulfilment`, `MergeFulfilments`, `MoveFulfilmentLines`) and both validation rules take `lockForUpdate()` on the rows they reason over, inside the action's transaction, so two concurrent requests cannot both pass validation against the same stale total.

#### Per-fulfilment lifecycle — `FulfilmentState` (transition-guarded)

A `Fulfilment` record is the **one** hand-driven machine in this spec (the merchant marks a parcel shipped). It mirrors the `OrderState` pattern from 0021 — abstract base reading a bound config — but is its own machine with its own contract.

```
States/Fulfilment/
├── FulfilmentState.php              ← abstract base
├── Pending.php                      ← 'pending'   (created, not yet actioned)
├── InProgress.php                   ← 'in-progress' (picking/packing)
├── Shipped.php                      ← 'shipped'   (sets shipped_at)
├── Cancelled.php                    ← 'cancelled'
└── Returned.php                     ← 'returned'
```

Default `Pending`. Graph (declared in `FulfilmentStateConfig`):

- `Pending → InProgress, Shipped, Cancelled`
- `InProgress → Pending, Shipped, Cancelled`
- `Shipped → Pending, Returned`
- `Cancelled` — terminal
- `Returned → Shipped` (undo a mistaken return)

`Returned → Shipped` is an **undo return**: a mistaken return is reversed back to `Shipped`, keeping the shipment (`shipped_at` + tracking) intact and the items counting as fulfilled again — surfaced as a dedicated "Undo return" ⋮ action (via `TransitionFulfilment`, which only clears `shipped_at` for *pre-ship* targets, so `Shipped` is untouched).

`Shipped → Pending` (and `InProgress → Pending`) is an **un-ship / cancel**: a mistaken progression can be reverted, which clears `shipped_at` and returns the parcel's items to the unfulfilled pool (the parcel becomes re-shippable). This is *not* surfaced as a normal step in the admin "Update status" menu — that menu is forward-only (`pending` and `cancelled` are excluded from it). Instead it is a deliberate, destructive **"Cancel fulfilment"** action (danger) in the parcel's ⋮ menu, shown for `in-progress` / `shipped` parcels, implemented via `TransitionFulfilment` (which un-stamps `shipped_at` when moving to a pre-ship state). A shipped parcel is reverted rather than terminally cancelled, since the split-down model has no manual "create fulfilment" to re-fulfil orphaned items; the terminal `Cancelled` state remains in core for programmatic use but is not surfaced in the admin.

`shipped_at` is stamped by `ShipFulfilment`, not by the state class (states stay model-coupling-free, per 0021's handover constraint); `TransitionFulfilment` clears it on an un-ship.

**Holds (Shopify-style).** A parcel can be put **on hold** — a flag (`held_at` + `hold_reason` + `hold_note`), *orthogonal* to the state graph rather than a state of its own, so a `Pending` or `InProgress` parcel can be held without losing its progress and releasing restores nothing (the state never moved). A held parcel is blocked from shipping (`ShipFulfilment::canRun()` is false and `execute()` throws `fulfilment_on_hold`); the admin hides "Shipped" from its menu while held. `HoldFulfilment` / `ReleaseFulfilment` (`Fulfilments::hold($f, $reason, $note)` / `Fulfilments::release($f)`, `FulfilmentHeld` / `FulfilmentReleased` events) toggle it. Reasons come from a configurable list in `config/fulfilment.php` (`hold_reasons`, key ⇒ label) shown as a dropdown plus a free-text note. The hold does **not** affect the order-level `fulfilment_status` rollup — a held parcel is simply still unfulfilled.

> **No `Delivered`.** The per-parcel lifecycle stops at `Shipped` — core has no delivery signal (carrier-tracking ingestion is a later concern). A `Delivered` state and `delivered_at` are deliberately omitted; reintroduce them when tracking ingestion is designed. The knock-on for the headline is in §D: `Complete` is a **manual** close, not an auto-derived "all delivered".

### B. Derived `payment_status` — rollup of `transactions`

A read-only (system-set) state, recomputed from the ledger. Shopify `financial_status` vocabulary:

```
States/Order/Payment/
├── PaymentState.php                 ← abstract base
├── Pending.php                      ← 'pending'            (no successful capture or authorization)
├── Authorized.php                   ← 'authorized'         (intent succeeded, nothing captured)
├── PartiallyPaid.php                ← 'partially-paid'     (0 < captured < total)
├── Paid.php                         ← 'paid'               (captured ≥ total, no refund)
├── PartiallyRefunded.php            ← 'partially-refunded' (0 < refunded < captured)
├── Refunded.php                     ← 'refunded'           (refunded ≥ captured > 0)
└── Voided.php                       ← 'voided'             (authorization released, nothing captured)
```

`payment_status` is **not** transition-guarded — it is a pure function of the ledger. `Actions/Orders/ResolvePaymentStatus` computes it:

```php
final class ResolvePaymentStatus implements ResolvePaymentStatusContract
{
    /** @return class-string<PaymentState> */
    public function execute(Order $order): string;
}
```

Logic (amounts are integer minor units; compare against `order.total`):

| Condition | Result |
|---|---|
| `captured ≥ total` and `refunded == 0` | `Paid` |
| `captured ≥ total` and `0 < refunded < captured` | `PartiallyRefunded` |
| `refunded ≥ captured` and `captured > 0` | `Refunded` |
| `0 < captured < total` | `PartiallyPaid` |
| `captured == 0` and authorized intent succeeded and not voided | `Authorized` |
| `captured == 0` and authorization voided/failed | `Voided` |
| otherwise | `Pending` |

where `captured = Σ successful captures`, `refunded = Σ successful refunds`, `authorized = Σ successful intents`. The `order_status` resolver (§D) treats a failed-only ledger (no success, an attempt made) as feeding `PaymentFailed`.

### C. Derived `fulfilment_status` — rollup of `Fulfilment` records

Order-level rollup over the fulfillable physical lines and what their fulfilments cover.

```
States/Order/Fulfilment/
├── FulfilmentStatus.php             ← abstract base
├── Unfulfilled.php                  ← 'unfulfilled'        (nothing shipped)
├── PartiallyFulfilled.php           ← 'partially-fulfilled'(some qty shipped, some outstanding)
├── Fulfilled.php                    ← 'fulfilled'          (all fulfillable qty shipped)
├── PartiallyReturned.php            ← 'partially-returned'
└── Returned.php                     ← 'returned'
```

`Actions/Orders/ResolveFulfilmentStatus` computes it from `fulfilment_lines` quantities (counting only fulfilments in `Shipped`/`Returned`) against the order's physical-line quantities. Digital-only orders with no physical lines resolve to `Fulfilled` (nothing to ship).

> **Amendment — fulfillability is no longer keyed on the `type` string.** [[0030-fulfillable-order-lines]] stamps `order_lines.requires_shipping` from `Purchasable::isShippable()` at creation; the rollup, initial fulfilment and quantity validation operate on `Order::fulfillableLines()` instead of `physicalLines()`. Read "physical lines" throughout this spec as "fulfillable lines".

> **Naming.** The order-level rollup is `FulfilmentStatus` (under `States/Order/Fulfilment/`); the per-parcel lifecycle is `FulfilmentState` (under `States/Fulfilment/`). Distinct machines, distinct folders — the rollup is derived and unguarded, the lifecycle is hand-driven and guarded.

### D. Headline `Order::$status` — derived, with manual overrides

`OrderState` keeps the 0021 class set and `$name` values. What changes is **how it is set**: it is now computed from `payment_status` × `fulfilment_status` by `OrderStateConfig::computeOrderStatus()`, **unless** the order is currently in a **manual override** state, which short-circuits the resolver and persists until the merchant transitions out.

```php
interface OrderStateConfig   // extends the 0021 contract
{
    // ... 0021 methods (orderStates, orderTransitions, defaultOrderState, notificationsFor) ...

    /** States the merchant sets by hand; these suppress derivation while active. */
    /** @return list<class-string<OrderState>> */
    public function overrideStates(): array;       // [OnHold, Cancelled, Refunded]

    /** Derive the headline from the two rollups. Not called while in an override state. */
    /** @return class-string<OrderState> */
    public function computeOrderStatus(PaymentState $payment, FulfilmentStatus $fulfilment): string;
}
```

`DefaultOrderStateConfig::computeOrderStatus()` resolver (manual overrides handled by the observer, not here):

| payment | fulfilment | → order_status |
|---|---|---|
| `Pending` / `Authorized` | any | `AwaitingPayment` |
| failed-only ledger | any | `PaymentFailed` |
| `PartiallyPaid` / `Paid` | `Unfulfilled` | `InProcess` |
| `Paid` | `PartiallyFulfilled` | `PartiallyShipped` |
| `Paid` | `Fulfilled` | `Shipped` |
| any | `Returned` / `PartiallyReturned` | `Returned` |
| `Refunded` | any | (override) `Refunded` |

**`Complete` is a manual close, not derived.** With no `Delivered` signal (§A), the resolver tops out at `Shipped`. `Complete` is reached by the merchant transitioning `Shipped → Complete` by hand (a "Mark complete" action) — the order is settled and done. It behaves like the other manual states in that the resolver never produces it; it is reached only through the guarded transition.

**`Backordered` is dropped in 0022.** It was in 0021's `OrderState` set but only ever as a hand-set placeholder; a real backorder is a stock concept, not a fulfilment one. 0022 removes the `Backordered` class and its transitions from the default config, to be reintroduced (derived from stock reservations) by a future stock/inventory spec. See Migration impact.

`computeOrderStatus` returns a class the merchant could also reach manually — but transitions are still validated against `orderTransitions()` (0021's graph) on write, so a derived jump that is not a legal transition throws and is logged rather than silently applied. The graph and the resolver must stay consistent; `DefaultOrderStateConfigTest` asserts every resolver output is reachable in the graph from the preceding state.

### E. Columns & casts

Baseline migration `2026_01_01_000028_create_orders_table.php` is edited in place (v2 pre-release; same rule as 0017–0021):

- **Drop `status`** (the headline `OrderState` column — removed per the amendment).
- Add `payment_status` string, indexed, default `pending`.
- Add `fulfilment_status` string, indexed, default `unfulfilled`.
- Add `closed_at` datetime, nullable, indexed (the open/closed archive flag; null = open).

```php
// Models/Order.php casts()
'payment_status'    => PaymentState::class,
'fulfilment_status' => FulfilmentStatus::class,
'closed_at'         => 'datetime',
```

PHPDoc: `@property PaymentState $payment_status`, `@property FulfilmentStatus $fulfilment_status`, `@property ?Carbon $closed_at`. `payment_status` / `fulfilment_status` are **stored** (so they are queryable/filterable/indexable) and kept fresh by the recompute observers (§F) — not computed on every read. `closed_at` is set/cleared by `CloseOrder` / `ReopenOrder`.

### F. Recompute observers

> Amended per the open/closed change: `RecomputeOrderStatus` now only recomputes the two rollups (there is no headline to derive). When a rollup value changes it fires `OrderPaymentStatusUpdated` / `OrderFulfilmentStatusUpdated`, which the notification listeners consume.

- **`TransactionObserver`** (new) — on `created`/`updated`/`deleted` of a `Transaction`, recompute the parent order's `payment_status` via `ResolvePaymentStatus`. Writes use `saveQuietly()` to avoid loops.
- **`FulfilmentObserver`** (new) — on any `Fulfilment` / `FulfilmentLine` change, recompute the order's `fulfilment_status` via `ResolveFulfilmentStatus`.
- **`OrderObserver`** — creates the initial fulfilment on placement (`placed_at` set). It no longer logs a status change or dispatches a headline event (the headline is gone).

`RecomputeOrderStatus::execute(Order $order)` centralises both rollups: resolve `payment_status` + `fulfilment_status`, `saveQuietly()`, and dispatch the per-rollup events on change.

Registration alongside the existing `Order::observe(...)` in `LunarServiceProvider::bootingPackage()`.

### G. Fulfilment operations — actions, manager & facade

Each fulfilment operation is a discrete **action** (the swappable seam per 0016), and a thin **`FulfilmentManager`** + **`Fulfilments`** facade gives the ergonomic, discoverable entry point — mirroring how `CartSession` / `Payments` / `Discounts` front their domains. The manager does **not** reimplement logic; it constructor-injects the action contracts and delegates, so swapping an action binding still takes effect through the facade.

#### Actions

Bound in `ActionServiceProvider::$actions`, each exposing a single `execute()` and returning the value the caller needs:

- `Actions/Fulfilment/CreateFulfilment` — `execute(Order $order, array $lines, array $attributes = []): Fulfilment`. `$lines` is `[order_line_id => quantity]`. Validates the quantity invariant (§A) via `Validation/Fulfilment/FulfilmentQuantity`, creates the `Fulfilment` (default `Pending`) + `FulfilmentLine` rows in a transaction, fires `FulfilmentCreated`. The observer recomputes `fulfilment_status`.
- `Actions/Fulfilment/SplitFulfilment` — `execute(Fulfilment $fulfilment, array $moves): Fulfilment`. Splits a pre-ship fulfilment (see mechanics below); returns the **new** fulfilment.
- `Actions/Fulfilment/MergeFulfilments` — `execute(Fulfilment $target, Collection $sources): Fulfilment`. Folds pre-ship sources into the target; returns the target.
- `Actions/Fulfilment/ShipFulfilment` — `Pending`/`InProgress` → `Shipped`; stamps `shipped_at`, records tracking (`tracking_number`, `tracking_url`, `shipping_method`).
- `Actions/Fulfilment/CancelFulfilment` — any non-terminal → `Cancelled`; returns its quantities to the order's unfulfilled pool (the rollup stops counting a cancelled fulfilment).
- `Actions/Fulfilment/ReturnFulfilment` — `Shipped` → `Returned`; feeds `PartiallyReturned` / `Returned` at the order level. Independent of refunds — returning never issues a refund and a refund never marks a return (§D).
- `Actions/Fulfilment/TransitionFulfilment` — `execute(Fulfilment $fulfilment, string $state): Fulfilment`. The plain guarded transition (`Fulfilments::transition()`) for moves that carry no extra behaviour — notably `Pending` → `InProgress`. The admin "Update status" menu lists `state->transitionableStateInstances()` and routes each target to its dedicated action where one exists — `Shipped` → `ShipFulfilment` (tracking form), `Cancelled` → `CancelFulfilment`, `Returned` → `ReturnFulfilment` — and uses `TransitionFulfilment` for the rest, so overriding a dedicated seam still takes effect from the admin.
- `Actions/Orders/ResolvePaymentStatus` / `Actions/Orders/ResolveFulfilmentStatus` — the rollup resolvers (§B/§C). Contracts in `Contracts/Orders/`.

All state-changing actions are transaction-wrapped and route their state change through the `Fulfilment`'s `FulfilmentState` (so the §A graph is enforced) — an illegal transition throws `CouldNotPerformTransition` and the action is a no-op.

#### Manager & facade

> **Amendment — the manager and facade are retired by [[0029-entry-point-conventions]].** `FulfilmentManager` / `Facades\Fulfilments` shipped as described below, but 0029 established that managers are reserved for state, context, drivers and registries — not stateless routing of model-first calls. The ergonomic entry points are now verb methods on `Order` (`createFulfilment()`, `cancel()`, `close()`, `reopen()`, `capture()`, `refund()`) and `Fulfilment` (`ship()`, `split()`, `merge()`, `moveLinesTo()`, `cancel()`, `markReturned()`, `transition()`, `hold()`, `release()`, `changeLocation()`, `addTracking()`), each delegating to the same action contracts — so the swappable seams below are unchanged. The section is kept for the action design it documents.

```php
namespace Lunar\Core\Managers;

class FulfilmentManager implements Contracts\FulfilmentManager
{
    public function __construct(
        protected CreateFulfilmentContract $create,
        protected SplitFulfilmentContract $split,
        protected MergeFulfilmentsContract $merge,
        protected ShipFulfilmentContract $ship,
        // …cancel, return
    ) {}

    public function create(Order $order, array $lines, array $attributes = []): Fulfilment { /* delegate */ }
    public function split(Fulfilment $fulfilment, array $moves): Fulfilment { /* delegate */ }
    public function merge(Fulfilment $target, Collection $sources): Fulfilment { /* delegate */ }
    public function ship(Fulfilment $fulfilment, array $tracking = []): Fulfilment { /* delegate */ }
    // …
}
```

`Contracts\FulfilmentManager` is the facade accessor; the impl is bound in `LunarServiceProvider::registerManagers()` (`$this->app->singleton(Contracts\FulfilmentManager::class, FulfilmentManager::class)`), and `Facades\Fulfilments` resolves it — so `Fulfilments::split($parcel, [$lineId => 4])` is the headline API.

`OrderStateConfig` stays a non-action service binding (0021), now also exposing the resolver + overrides. `FulfilmentStateConfig` (§A) is registered the same way.

#### Split mechanics

`Fulfilments::split($source, [$orderLineId => $qtyToMoveOut, …])` reorganises **before dispatch** — it never changes how much is fulfilled, only how the outstanding quantities are parcelled:

1. Guard: `$source` must be in `Pending` or `InProgress` (a `Shipped` parcel has physically left; you split a return instead, not the shipment).
2. For each move, assert `$qtyToMoveOut ≤` the source `FulfilmentLine.quantity` for that order line.
3. Decrement the source line by `$qtyToMoveOut` (delete the source line if it reaches 0).
4. Create a new `Pending` `Fulfilment` on the same order with `FulfilmentLine` rows for the moved quantities.
5. Return the new fulfilment. Example: source covers line X qty 10 → `split($source, [X => 4])` leaves the source at 6 and returns a new fulfilment carrying 4, which can be shipped independently. (This is the 4 + 6 split.)

Because split only moves *outstanding* quantity between parcels, `fulfilment_status` and the headline are unchanged by it; the §A sum invariant holds by construction.

#### Merge mechanics

`Fulfilments::merge($target, $sources)` is the inverse — consolidate parcels that haven't shipped:

1. Guard: `$target` and every source belong to the **same order** and are all in `Pending`/`InProgress` (terminal/shipped fulfilments cannot merge).
2. For each source `FulfilmentLine`, add its quantity onto the target's matching (`order_line_id`) line — creating the target line if absent (respecting `UNIQUE (fulfilment_id, order_line_id)`).
3. Delete the absorbed source fulfilments. Tracking/`shipping_method` on sources is discarded (the target's wins); the action errors if sources carry conflicting tracking that would be silently lost — surfaced for the caller to resolve.
4. Return the target.

Like split, merge preserves total fulfilled quantity, so the rollups are untouched.

### H. Events

- `Events\OrderPaymentStatusUpdated($order, $previous, $new)` and `Events\OrderFulfilmentStatusUpdated($order, $previous, $new)` — mirror 0021's `OrderStatusUpdated`, fired by the recompute observers when the respective column changes. `OrderStatusUpdated` continues to fire for the headline.
- `Events\FulfilmentCreated($fulfilment)` and `Events\FulfilmentStatusUpdated($fulfilment, $previous, $new)` — for the per-parcel lifecycle.

`SendOrderStatusNotifications` (0021) is unchanged; consumers may additionally key notifications off the new payment/fulfilment events.

### I. Config

`config/orders.php` gains documented (commented) keys for the new states' notifications, reusing the 0021 pattern:

```php
'notifications' => [
    // 'paid'      => [App\Notifications\PaymentReceived::class],   // payment_status
    // 'fulfilled' => [App\Notifications\OrderFulfilled::class],     // fulfilment_status
    // 'shipped'   => [App\Notifications\OrderShipped::class],       // order status (0021)
],
```

`notificationsFor()` already resolves by `$state::$name`; the same flat-key lookup covers all three machines.

### J. `OrderStateCategory` enum (reintroduced)

`Enums/OrderStateCategory` groups headline states for admin colour/filtering: `Unpaid`, `Processing`, `Shipped`, `Completed`, `Cancelled`, `Returned`. Each `OrderState` declares `category(): OrderStateCategory`. Drives the badge colour in the Filament order resource and a category-level list filter. (Dropped from 0021; lands here where the states it groups are derived.)

### K. Filament / admin

- Order resource: three badges — `status` (headline, coloured by `OrderStateCategory`), `payment_status`, `fulfilment_status` — replacing the single 0021 badge. The headline badge is **read-only** (derived); `payment_status` / `fulfilment_status` are read-only too. Manual overrides (`Place on hold` / `Cancel` / `Resume`) plus the manual close (`Mark complete`, `Shipped → Complete`) stay as actions on the header (extending 0021), now the only writable paths into `status`.
- New **Fulfilments** relation manager on the order: list parcels with their `FulfilmentState` badge, `Create fulfilment` (pick lines + quantities, gated by the outstanding quantity per line), `Split` (move a quantity into a new parcel), `Merge` (consolidate selected pre-ship parcels), and `Mark shipped` / `Cancel` / `Return` actions reflecting the `FulfilmentState` graph (each offered only when the transition is legal). All delegate to the `Fulfilments` facade so the admin and an API consumer share one path.
- Order-list filters: `status` (category-aware), plus new `payment_status` and `fulfilment_status` single-column `where` filters.
- Verify end-to-end against the host app at `https://lunar-v2.test` (Herd) per package convention.

## Alternatives considered

- **Whole-order fulfilment (status flag, no line records).** Rejected (chosen against in scoping): a boolean-ish `fulfilled` flag cannot represent partial/split shipments, and `PartiallyShipped` would remain the meaningless hand-set state 0021 flagged. Line-item fulfilments make partials real.
- **Fully derived headline with no manual states** (move `OnHold`/`Cancelled` to separate columns). Rejected: it splits the lifecycle across nullable flags — the exact thing 0021 rejected ("A single `status` machine keeps the lifecycle in one place"). Manual overrides as first-class states that suppress derivation keep one column authoritative.
- **Keep `status` hand-driven, add payment/fulfilment as purely informational columns.** Rejected: it leaves the drift problem (§Problem) — the headline still has to be hand-synced to the facts. Deriving it is the point.
- **Compute `payment_status` / `fulfilment_status` on read (no stored column).** Rejected: they must be filterable/indexable for order lists and reporting; recompute-on-write via observers is cheap (transactions/fulfilments change rarely relative to reads) and keeps the columns authoritative.
- **Lean payment vocabulary** (`unpaid/partially-paid/paid/refunded`). Rejected in scoping: the `intent` vs `capture` transaction types already carry the authorized/voided distinction, so the Shopify set costs nothing extra to derive and is the vocabulary integrators expect.
- **One shared state machine for order + fulfilment** (literally reuse `OrderState` bound to `OrderStateConfig`). Rejected: the per-parcel lifecycle needs its own transition graph and seam; a sibling `FulfilmentState` + `FulfilmentStateConfig` following the identical pattern is cleaner than overloading one config. (Refines 0021's "reuse the same base" handover note — same *pattern*, not the same config-bound class.)

## Migration impact

> **Open/closed amendment.** The headline `OrderState` removal changes the migration impact below: the `orders.status` column is **dropped** (not kept), a nullable indexed `closed_at` is **added**, and all `OrderState` / `OrderStateConfig` / order-status-action surface is **removed** rather than extended. v1 → v2: a v1 order's headline maps to open/closed — historically "complete"/"cancelled"/"refunded" orders backfill as **closed** (`closed_at` = the order's updated/placed time), everything else **open**; the merchant re-derives money/fulfilment from the rollups. `States\Order\Order\*`, `OrderStateCategory`, `OrderStateConfig`, `MarkOrderAsComplete/Shipped`, `UpdateOrderStatus`, and the Filament Cancel/Hold/Resume/MarkComplete/MarkShipped/UpdateStatus actions are removed (no Rector target — flagged for manual migration); `states.order.*` collapses to `{open,closed}`.

- **Baseline migrations edited in place** (v2 pre-release):
  - `..._create_orders_table.php` — **drop `status`**; add `payment_status` (default `pending`, indexed), `fulfilment_status` (default `unfulfilled`, indexed), and `closed_at` (nullable, indexed).
  - **New** baseline migrations `..._create_fulfilments_table.php` and `..._create_fulfilment_lines_table.php` (§A). New tables, so genuinely new files even under the in-place rule.
  - **New** baseline migration `..._create_locations_table.php` (§A Locations), numbered before the fulfilments baseline so `location_id` is declared inline in `..._create_fulfilments_table.php` with its FK resolvable — no separate add-column migration needed.
- **No core data migration.** v2 has no live data. `InstallLunar` seeds the `Default` location.
- **Data migration (stage 3, `packages/upgrade`):**
  - Derive v1 orders' `payment_status` from their transaction ledger using the §B logic (one-way; [[feedback-upgrade-migrations-no-down]]).
  - v1 had no line-item fulfilments; backfill `fulfilment_status` from the v1 headline (`dispatched`→`fulfilled`, else `unfulfilled`) and create no `Fulfilment` rows for historical orders (or one whole-order `Fulfilment` per shipped order — finalised in the upgrade PR).
- **Breaking changes to the public contract surface:**
  - `Order::$status` is now **derived** (system-set via observers) rather than freely hand-set. Direct `$order->status->transitionTo(Shipped::class)` still works for override states but a derived state set by hand may be overwritten on the next recompute. Documented; Rector note in `LunarSetList`.
  - **`Backordered` `OrderState` removed.** The `States\Order\Order\Backordered` class shipped in 0021 is deleted along with its transitions in `DefaultOrderStateConfig`; reintroduced by a future stock spec. Consumers transitioning to it break — Rector rule maps `Backordered::class` references to `OnHold::class` (the nearest "blocked" override) with a note. The `states.order.backordered` translation key is removed from all 16 locales.
  - New `payment_status` / `fulfilment_status` properties + casts on `Order`.
  - New `Location` model + `Models\Contracts\Location` + `location_id` on `Fulfilment` (required FK, `restrictOnDelete` — a location with fulfilments cannot be deleted). Additive. Fulfilment operations are now location-aware: `Fulfilments::move()` is a new manager/facade verb, and merge/move refuse to combine fulfilments from different locations.
  - New `Contracts\Fulfilment`, `Contracts\FulfilmentManager`, `Managers\FulfilmentManager`, `Facades\Fulfilments`, `Contracts\FulfilmentStateConfig`, the `Actions/Fulfilment/*` set + their contracts, `Contracts\Orders\ResolvePaymentStatus`, `Contracts\Orders\ResolveFulfilmentStatus`, and the extended `OrderStateConfig` (adds `overrideStates()` + `computeOrderStatus()`). All additive except the `OrderStateConfig` extension: consumers who implemented it directly (rather than extending `DefaultOrderStateConfig`) must add the two methods — call out in the upgrade guide.
  - `config('lunar.orders.notifications.*')` now also keys off payment/fulfilment `$name`s.
- **Translation / locale impact (16 locales).** New keys, English first then mirrored placeholders across the other 15:
  - `states.payment.{pending,authorized,partially-paid,paid,partially-refunded,refunded,voided}`
  - `states.fulfilment-status.{unfulfilled,partially-fulfilled,fulfilled,partially-returned,returned}`
  - `states.fulfilment.{pending,in-progress,shipped,cancelled,returned}` (per-parcel lifecycle; no `delivered`)
  - `enums.order-state-category.{unpaid,processing,shipped,completed,cancelled,returned}`
  - Plus Filament labels for the fulfilments relation manager + actions.
  - **Removed:** `states.order.backordered` (state dropped, above).
- **Filament / admin impact** — §K.

## Open questions

None outstanding — resolved during review (2026-06-04):

- **Backordered.** Dropped from 0022 entirely (not a fulfilment concept); reintroduced by a future stock/inventory spec. (§D, Migration impact.)
- **`Delivered`.** Dropped for now — the per-parcel lifecycle stops at `Shipped`; `Complete` becomes a manual close rather than an auto-derived "all delivered". Reintroduce with carrier-tracking ingestion. (§A, §D.)
- **Refund vs return.** Fully independent — a return never auto-refunds and a refund never auto-marks a return. (§D, `ReturnFulfilment` in §G.)
- **Notification config.** Stays at `lunar.orders.notifications` — no relocation. (§I.)
- **Over-fulfilment on edited lines.** An order line's `quantity` cannot be reduced below the total already fulfilled, enforced by a `Validation/Order/OrderLineQuantity` rule (not just checked at fulfilment-creation). (§A.)

## References

- [[0021-state-machines]] — establishes `OrderState`, `OrderStateConfig`, `OrderObserver`, `OrderStatusUpdated`, the `States/` folder, and the explicit deferral this spec picks up.
- [[0013-base-directory-reorganisation]] — `States/` one-machine-per-subfolder; contracts under `Contracts/` with no `Interface` suffix.
- [[0016-service-layer-di]] — actions in `ActionServiceProvider::$actions`; `OrderStateConfig` / `FulfilmentStateConfig` bound in `registerServices()`.
- `2026_01_01_000053_create_transactions_table.php` — the ledger `payment_status` derives from.
- `spatie/laravel-model-states` v2 — https://spatie.be/docs/laravel-model-states/v2
- Shopify `financial_status` / `fulfillment_status` — vocabulary prior art.
- `packages/upgrade` — Rector rules + v1→v2 data migration for the columns/API above.
