# 0038 — Inventory fundamentals

- Status: proposed
- Author: Glenn Jacobs
- Created: 2026-06-25
- TODO item: Inventory

## Problem

Stock today is two integer columns on `ProductVariant` — `stock` and `backorder` — plus a `purchasable` mode string (`always`, `in_stock`, `in_stock_or_backorder`). `getTotalInventory()` and `canBeFulfilledAtQuantity()` read those columns; `CartLine\CartLineStock` validates against them at add-to-cart time; and the only write seam is `AdjustStock::execute(variant, delta, reason)`, which force-fills `stock` and writes an activity-log line. There is:

- **No location dimension.** A `Location` model exists (a warehouse or store), every `Fulfilment` is stamped to one, and its docblock already says inventory "is tracked against" it in future — but no table joins a variant to a location. A two-warehouse merchant cannot say where the 40 units are.
- **One flat number.** `stock` conflates physically-present, sold-but-not-shipped, and damaged-not-sellable. There is no notion of stock committed to open orders, stock held back, stock held for an in-flight checkout, or stock incoming on a purchase order.
- **No audit trail.** `stock` is a mutable counter. The activity log records manual `AdjustStock` calls only; there is no append-only record of how a level reached its value.
- **No automatic movement.** Placing an order does not touch stock. Shipping a fulfilment does not touch stock. Returning one does not. Stock only ever changes when a human clicks adjust, so the headline drifts from reality the moment any order ships.
- **Deferred IOUs from the fulfilment work.** Three shipped/accepted specs parked their stock behaviour here:
  - [[0025-order-cancellation]] — restock-on-cancel omitted "until the inventory spec".
  - [[0028-line-item-refunds]] — refund page "will grow a restock toggle then".
  - [[0022-order-fulfilments]] — the `Backordered` order state was removed, "to be reintroduced (derived from stock reservations) by a future stock/inventory spec".
- **A stop-gap action.** [[0009-filament-actions-and-global-search]] shipped `AdjustStock` / `AdjustStockAction` against the raw columns, explicitly "superseded when Inventory lands".

The goal of this spec is the **fundamentals**: a real per-location quantity model with an audit ledger, a denormalised global rollup so a variant has a single answer to "how many can I sell", the movement seams that ship / return / cancel / restock all call, and a reservation seam a future checkout can hold stock against.

## Proposal

Three new Eloquent models on top of the existing `Location`, plus a denormalised global rollup cached on `ProductVariant`:

1. **`StockLevel`** — the per-location balance across the physical / committed / held buckets.
2. **`StockMovement`** — the immutable, append-only ledger behind the physical `on_hand` figure.
3. **`StockReservation`** — a time-boxable hold against a variant for an in-flight checkout (the seam; checkout wiring is a follow-on).
4. **Rollup columns on `ProductVariant`** — the global, queryable sum (and the home for unallocated commitments and reservations).

### The six quantities

Per location where noted, and rolled up globally per variant:

| Quantity | Meaning | Scope | Maintained by |
|---|---|---|---|
| `on_hand` | physically present units | per-location + rollup | the `StockMovement` ledger (received / shipped / returned / adjusted) |
| `incoming` | on a purchase order, expected in | per-location + rollup | a plain manual field core stores; driven by a purchasing/inventory add-on, not core |
| `committed` | allocated to open orders, not yet dispatched | global-first (rollup), allocated subset per-location | order lifecycle — placed (+), dispatched/cancelled (−) |
| `reserved` | held for an in-flight checkout, not yet an order | global only (rollup) | the reservation seam — reserve (+), release/expire (−), commit (→ committed) |
| `unavailable` | held back — damaged, reserved, not sellable | per-location + rollup | manual hold / release |
| `available` | **computed** = `on_hand − committed − reserved − unavailable` | computed | not stored at location; cached + indexed on rollup |

`incoming` is excluded from `available` — it is not sellable right now. Selling against it is a backorder policy (`backorder` column + `purchasable` mode), unchanged by this spec.

`committed` and `reserved` are both **global-first** because neither has a location at the moment it is created — see below. `reserved` lives only on the rollup (a cart never picks a location), so the per-location `available` accessor is `on_hand − committed − unavailable` and the *sellable* figure is always the global rollup.

**Naming note.** Our `reserved` is a *checkout hold* — the transient claim a paying customer places, the equivalent of Shopify's payment-time reservation (held for minutes, then committed or released). It is **not** the same as Shopify's *displayed* "reserved" inventory state, which is a manual set-aside (wholesale, photoshoot, promo) and maps to our `unavailable`. In this model, held-back-for-a-reason stock is always `unavailable`; `reserved` is only ever an in-flight checkout.

### Why `committed` and `reserved` are global-first

A commitment is created when an order is **placed**, but an order has no location until a `Fulfilment` is created ([[0022-order-fulfilments]]). A reservation is created mid-checkout, earlier still. So at neither moment can we attribute the quantity to a specific location. This is the case for merchants who want to track stock yet let customers order without choosing a fulfilment location.

The model resolves this by tracking these buckets **globally first, allocated to a location second**:

- **Stock reserved** (future checkout) → the variant's *global* `reserved` rises. Global `available` drops; no order, no location yet.
- **Order placed** → the variant's *global* `committed` rises. If the order carried a reservation, that reservation is *committed* in the same step (`reserved −= qty`, `committed += qty`) so global `available` does not blip. No location row is touched; the sellable check works with no location chosen.
- **Fulfilment created at location L** → the commitment is *allocated*: `L.committed` rises. The global figure already counts it — `L.committed` is the allocated subset, never additive on top of global.
- **Ship** → `on_hand` and `committed` both drop by the shipped quantity, at L and globally. Global `available` is unchanged by shipping (the unit was already committed, never available).
- **Fulfilment cancelled before ship** → the allocation is released back to the unallocated pool: `L.committed` drops, **global `committed` is unchanged** (the order line is still open and will be re-fulfilled).
- **Order cancelled before ship** → the *global* commitment is released (`committed` drops, `on_hand` untouched). This is what makes [[0025-order-cancellation]]'s restock-on-cancel work: it is a commitment release, not a physical movement.

Invariant: `global.committed >= sum(location.committed)`; the gap is commitments not yet allocated to any location. Distinguishing **order-cancel** (global release) from **fulfilment-cancel** (location de-allocation) is what keeps this invariant true — conflating them would under-count global `committed` and let a line be over-committed when re-fulfilled.

### Model A — `StockLevel`

Table `lunar_stock_levels`:

| Column | Type | Notes |
|---|---|---|
| `id` | bigint | |
| `product_variant_id` | FK → `product_variants` | |
| `location_id` | FK → `locations` | |
| `on_hand` | integer, default 0 | ledger-derived running balance |
| `incoming` | integer, default 0 | |
| `committed` | integer, default 0 | the allocated subset at this location |
| `unavailable` | integer, default 0 | |
| `meta` | jsonb, nullable | |
| `created_at` / `updated_at` | timestamps | |

- Unique composite index on (`product_variant_id`, `location_id`).
- No `reserved` column — reservations are global-only (carts never pick a location).
- `available` is an accessor, not a column: `on_hand - committed - unavailable` (the allocatable-physical figure at this location; the *sellable* figure is the global rollup).

```php
/**
 * @property int $on_hand
 * @property int $incoming
 * @property int $committed
 * @property int $unavailable
 * @property-read int $available
 */
class StockLevel extends Base implements Contracts\StockLevel
{
    public function variant(): BelongsTo;     // ProductVariant
    public function location(): BelongsTo;    // Location
    public function movements(): HasMany;     // StockMovement
    public function getAvailableAttribute(): int;
}
```

### Model B — `StockMovement`

The ledger behind `on_hand` only — the one bucket that most needs an immutable, reconstructable trail. `committed`, `reserved` and `unavailable` are maintained counters reconstructable from order lines / reservation rows / hold actions, so they are not double-ledgered here (see Alternatives — uniform-bucket ledger).

Table `lunar_stock_movements` (append-only):

| Column | Type | Notes |
|---|---|---|
| `id` | bigint | |
| `product_variant_id` | FK → `product_variants` | denormalised for cheap per-variant history |
| `location_id` | FK → `locations` | |
| `quantity` | integer | **signed** delta applied to `on_hand` |
| `type` | string (enum-backed), indexed | `StockMovementType` |
| `source_type` / `source_id` | nullable morph | originating record — `Fulfilment`, refund `Transaction`, … |
| `note` | string, nullable | |
| `causer_type` / `causer_id` | nullable morph | who triggered it |
| `created_at` | timestamp | the movement instant |

`StockMovementType` (PHP enum, TitleCase keys): `OpeningBalance`, `Received`, `Shipped`, `Returned`, `Adjustment`. Small set; the `source` morph and `note` carry specifics. Reversals (un-ship, undo-return) are recorded as new signed movements of the same type, not deletes — the ledger stays append-only.

### Model C — `StockReservation`

A time-boxable hold a checkout can place against a variant *before* an order exists, so two concurrent checkouts cannot both claim the last unit. This spec ships the **model, the seam, and the expiry mechanism**; wiring an actual checkout to reserve (cart association, add-to-cart-vs-checkout timing, admin visibility of held stock, scheduler cadence) is a **follow-on** (see Out of scope), exactly as location routing builds additively on `StockLevel`.

Table `lunar_stock_reservations`:

| Column | Type | Notes |
|---|---|---|
| `id` | bigint | |
| `product_variant_id` | FK → `product_variants` | global — no `location_id` (a cart picks no location) |
| `quantity` | integer | positive |
| `reference_type` / `reference_id` | nullable morph | the holder — a `Cart` / checkout session in the follow-on |
| `expires_at` | timestamp, nullable, indexed | null = no auto-expiry; must be released or committed explicitly |
| `released_at` | timestamp, nullable | |
| `committed_at` | timestamp, nullable | set when the reservation converts to a commitment at order placement |
| `note` | string, nullable | |
| `created_at` / `updated_at` | timestamps | |

- **Active** = `released_at IS NULL AND committed_at IS NULL AND (expires_at IS NULL OR expires_at > now)`.
- `stock_reserved` on the rollup is the sum of active reservations' `quantity` — a counter, reconstructable from these rows, so it is not ledgered.

```php
/**
 * @property int $quantity
 * @property ?\Illuminate\Support\Carbon $expires_at
 * @property ?\Illuminate\Support\Carbon $released_at
 * @property ?\Illuminate\Support\Carbon $committed_at
 * @property-read bool $is_active
 */
class StockReservation extends Base implements Contracts\StockReservation
{
    public function variant(): BelongsTo;     // ProductVariant
    public function reference(): MorphTo;     // the holder (a Cart, in the follow-on)
    public function release(): static;        // delegates to ReleasesReservation
    public function commit(): static;         // delegates to CommitsReservation (converts to a commitment at placement)
}
```

### Global rollup on `ProductVariant`

Replace the single `stock` column with cached rollup columns, maintained whenever any of the variant's `StockLevel` rows, its global committed, or its active reservations change:

| Column | Type | Notes |
|---|---|---|
| `stock_on_hand` | integer, default 0 | `sum(stockLevels.on_hand)` |
| `stock_incoming` | integer, default 0 | `sum(stockLevels.incoming)` |
| `stock_committed` | integer, default 0 | total commitments (allocated **and** unallocated) |
| `stock_reserved` | integer, default 0 | sum of active reservations |
| `stock_unavailable` | integer, default 0 | `sum(stockLevels.unavailable)` |
| `stock_available` | integer, default 0, indexed | `stock_on_hand − stock_committed − stock_reserved − stock_unavailable` |

`stock_committed` and `stock_reserved` are the two figures that are **not** pure sums of location rows — `committed` carries unallocated commitments (orders placed before a fulfilment picks a location) and `reserved` has no location at all. `stock_available` is indexed and may go negative (oversell — see Resolved decisions); it is what storefront "in stock?" filters and product-list sorting read, exactly as the old `stock` column was indexed. A `lunar:stock:reconcile` command rebuilds `on_hand` from the ledger, `reserved` from active reservations, and the rest of the rollup from the levels + open orders.

### Write seams

All `on_hand` changes go through one action so the ledger can never be bypassed:

```php
interface RecordsStockMovement
{
    public function execute(
        ProductVariant $variant,
        Location $location,
        int $quantity,                 // signed
        StockMovementType $type,
        ?Model $source = null,
        ?string $note = null,
    ): StockMovement;
}
```

It locks the (variant, location) `StockLevel` (creating it at zero if absent), appends the movement, bumps `on_hand`, refreshes the variant rollup — all in one transaction. The counter buckets get their own thin actions, each updating the counter + rollup transactionally:

- `CommitsStock` / `ReleasesStock` — order commitments (global; `AllocatesCommitment` / `DeallocatesCommitment` move the allocated subset between global and a location).
- `HoldsStock` / `ReleasesHold` — the `unavailable` bucket.
- `ReservesStock` / `ReleasesReservation` / `CommitsReservation` — the reservation lifecycle:

```php
interface ReservesStock
{
    public function execute(
        ProductVariant $variant,
        int $quantity,
        ?\DateTimeInterface $expiresAt = null,
        ?Model $reference = null,      // the holder — a Cart in the follow-on
        ?string $note = null,
    ): StockReservation;
}
```

`ReleasesReservation` marks a reservation released and drops `stock_reserved` (idempotent — a no-op if already released/committed). `CommitsReservation` converts a reservation at order placement: stamps `committed_at`, `stock_reserved −= qty`, `stock_committed += qty` — net `available` unchanged.

Model-first ergonomic verbs on the variant / reservation and their contracts, one-line-delegating to the actions:

```php
$variant->adjustStock($location, $delta, $type, source: $fulfilment, note: '...');
$variant->holdStock($location, $qty, note: 'damaged');
$reservation = $variant->reserveStock($qty, expiresAt: now()->addMinutes(30), reference: $cart);
$reservation->release();   // or, at placement:
$reservation->commit();
```

`AdjustStock` (the manual admin verb from [[0009-filament-actions-and-global-search]]) is **superseded**: its signature gains a `Location` (defaulting to `Location::getDefault()`) and it records a movement instead of force-filling `stock`.

### Sellability rewiring

`getTotalInventory()` returns `stock_available` (plus `backorder` when the `purchasable` mode allows it), so `canBeFulfilledAtQuantity()` and `CartLineStock` keep working unchanged in logic but now respect committed, reserved and held-back stock. Availability is the **global** figure summed across all locations — one number, matching today's single-column behaviour. The per-location buckets this spec lays down are deliberately the substrate for a planned follow-on: selling only what a specific location holds (click-and-collect to one store, a channel bound to one warehouse) via a channel/region-to-location routing layer resolved at sell-time. That follow-on is tracked (see Out of scope and References); the fundamentals make it additive — a routing resolver reads the same `StockLevel` rows, no schema change. `backorder` and `purchasable` stay on the variant — selling policy, not physical quantity.

The add-to-cart `CartLineStock` check stays **advisory**: with no reservation taken at add-to-cart, stock can still sell out before placement. The `ReservesStock` seam is the supported way for a checkout to *hold* stock (optionally time-boxed) and convert it to a commitment at placement, closing the oversell window for flows that opt in.

### Custom purchasables — the `TracksStock` capability

Anything sold is a `Purchasable` (a morph on cart/order lines); `ProductVariant` is the only one core ships. "Sellable" and "stock-tracked" are separate concerns, and the contract surface keeps them separate:

- **The availability read is already polymorphic.** `Purchasable::canBeFulfilledAtQuantity()` / `getTotalInventory()` are answered by each purchasable however it likes — a gift card says "always", an event ticket says "yes if `n <= remaining`", a variant delegates to its rollup. Nothing here changes that.
- **Stock participation is an opt-in capability**, `Contracts\TracksStock`. It carries the **global-first, location-agnostic** operations the cart / checkout / order-lifecycle call polymorphically — `commitStock` / `releaseCommittedStock` (placement, cancel), `reserveStock` / `releaseReservation` / `commitReservation` (checkout). The hooks operate through `instanceof TracksStock` and skip any purchasable that does not track stock (gift cards, digital, services, shipping lines), so the canonical committed predicate generalises from "is a variant" to "tracks stock and requires fulfilment".
- **The location-based ledger stays `ProductVariant`-specific.** `adjustStock(Location, …)`, `stockLevels`, `stockMovements` and the rollup are the *implementation* `ProductVariant` uses to satisfy `TracksStock` (its default trait is `HasStock`). They are deliberately not on the capability — `on_hand at a location` is a warehouse concept, and physical movement only happens through fulfilment, which `requires_fulfilment` already gates to physical variant-backed lines.

A custom stock-tracked purchasable (event seats, a bundle resolving to components, an external WMS, a marketplace vendor listing) implements `TracksStock` with **its own storage** — its stock rarely looks like `integer on_hand at a location`, so reusing `StockLevel` would be wrong anyway. It does not get the built-in engine for free; it gets the seam the cart and checkout call. `commitStock` returns nothing the caller needs; `reserveStock` returns a `Models\Contracts\StockReservation` handle (a behavioural interface — `release()` / `commit()`), so a custom implementer satisfies it with its own object rather than minting a core `StockReservation` row.

### Events and search

`on_hand` movements and rollup refreshes fire a `StockMovementRecorded` event and a variant-level stock-changed event. Because `stock_available` backs storefront "in stock?" facets — which on Scout/Meilisearch are an **index**, not a live column — the variant is re-indexed when `stock_available` changes, otherwise engine-backed availability filters go stale the moment any order ships. This rides the existing `Searchable` concern and ties into the planned "events, incl. cache invalidation" work; the events are the cache-invalidation seam for consumers too.

### Automatic movements (the hooks)

| Trigger | Effect |
|---|---|
| Checkout reserves (follow-on) | `reserved += qty` globally (variant rollup); `StockReservation` row with optional `expires_at` |
| Reservation expires / released | `reserved −= qty` |
| Order placed | `committed += qty` globally; if a reservation backed it, `CommitsReservation` (`reserved −= qty`) in the same transaction. Fires **once** per placement (see idempotency) |
| `Fulfilment` created at L | allocate: `L.committed += qty` (global unchanged) |
| `ShipFulfilment` | `Shipped` movement `−qty` at L; `committed −= qty` at L and globally |
| Un-ship (reverse of ship, [[0022-order-fulfilments]]) | `Shipped` movement `+qty` at L; `committed += qty` at L and globally |
| `ReturnFulfilment` | `Returned` movement `+qty` at L |
| Undo-return (reverse of return, [[0022-order-fulfilments]]) | `Returned` movement `−qty` at L |
| Fulfilment cancelled before ship | de-allocate: `L.committed −= qty` (global `committed` and `on_hand` unchanged) |
| Order cancelled before ship | release: global `committed −= qty` (no `on_hand` change) |
| Refund-with-restock ([[0028-line-item-refunds]]) | `Returned`/`Adjustment` `+qty`, `source = refund Transaction`, location chosen on the refund page |
| Manual adjust / hold (admin) | `Adjustment` / `unavailable ±` at a chosen location |

**Idempotency.** `committed`, `reserved` and `unavailable` are maintained counters with no ledger to catch drift, so each hook must apply exactly once. Order placement commits on the *placed* state transition (guarded against the `CreateOrder` update path / pipeline re-runs), and ship/return commitment moves ride the corresponding fulfilment state transitions (each fires once). `CommitsReservation` / `ReleasesReservation` guard on `committed_at` / `released_at`. The canonical predicate below makes a recompute-and-set possible where a guard is awkward, with `lunar:stock:reconcile` as the backstop.

**Canonical "committed" predicate.** Exactly one definition of *which order lines contribute to `committed` for a variant* lives in one place, called by both the live hooks and `lunar:stock:reconcile` so they can never disagree: a line commits when its order is **placed and not cancelled**, the line is **physical and variant-backed** (`order_lines.requires_fulfilment` from [[0031-fulfilment-methods]], skipping digital / shipping / non-variant lines), reduced by the quantity already shipped. Reconcile rebuilds the counter from this predicate; the hooks move it incrementally between reconciles.

### Out of scope

- **Checkout reservation wiring** — *this spec ships the `StockReservation` model, the `ReservesStock`/`ReleasesReservation`/`CommitsReservation` seam, and the `lunar:stock:release-expired` sweep.* Making a cart/checkout actually reserve (when it reserves, the TTL policy, cart association, admin visibility of held stock, registering the sweep on the consumer's scheduler) is the **planned follow-on**, tracked in `TODO.md`; the seam here is built so it lands additively. Likely shape: reserve at **payment/checkout start, not add-to-cart** (Shopify's native behaviour — avoids holding stock for abandoned carts), with the optional TTL covering the payment window; a ticket-sales "held for a few minutes while you pay" flow is the canonical case.
- **Allocation / location-selection routing** — which location a commitment is assigned to. This spec allocates to the fulfilment's location when one is created, nothing smarter. The follow-on covers both **system routing** (auto-assign a placed order to a location, splitting across several when needed — Shopify's fulfillment-order routing is the reference model) and **customer-driven selection at checkout** (the buyer picks a collection store / preferred location, and that choice drives allocation). The global-first `committed` bucket here is precisely the holding pen this routing later resolves into per-location allocations.
- **Incoming automation** — purchase orders / goods-in. `incoming` is a manual field core stores; populating it belongs to a purchasing/inventory add-on, not core.
- **Location-scoped storefront availability** — selling only what a specific location holds, via channel/region-to-location routing rules resolved at sell-time. **Planned follow-on**, tracked in `TODO.md`; the per-location `StockLevel` rows here are its substrate, so it adds a routing resolver without a schema change.
- **`Backordered` order state** — derived from committed-vs-on-hand ([[0022-order-fulfilments]]); follow-on.
- **Returned-goods quarantine** — returns and refund-restock land in sellable `on_hand`; routing them through `unavailable` for inspection first is a policy left to the planned RMA work.
- **Stock transfers between locations / low-stock thresholds and notifications.**
- **Polymorphic stockable** — see Alternatives.

## Alternatives considered

- **`on_hand` only, reservations later (the prior draft of this spec).** Simpler, but `committed` is what makes "available" meaningful and what lets restock-on-cancel work; deferring it leaves the headline number nearly as blind as today. Superseded by this revision at the author's direction.
- **Reservation as a short-lived `committed` entry (no separate bucket).** Reuses the commitment machinery, but the canonical committed predicate is "open order lines", and a cart reservation has no order line — folding it in would corrupt reconcile. A distinct `reserved` bucket keeps reconcile clean. Rejected.
- **Global rollup as a separate `StockSummary` table (third model).** Keeps the variant table narrow and localises the per-placement write lock to a dedicated row, but adds a model and a join for the most common read ("is this sellable"). Rejected: cached columns on the variant keep the sellable figure on an indexed column the storefront already filters/sorts by. The lock cost is accepted by keeping the rollup write a single narrow `UPDATE ... SET stock_committed = stock_committed + ?` held only for the duration of the placement transaction, never across the order pipeline (see contention note in Resolved decisions).
- **Uniform-bucket ledger** — `StockMovement` carries a `bucket` (`on_hand` / `committed` / `reserved` / `unavailable`) and every bucket change is ledgered. Fully auditable and elegant, but `committed`/`reserved` churn on every order and every checkout, multiplying ledger write volume, and are already reconstructable from open order lines / active reservations. Rejected for the fundamentals; revisit if committed/reservation history is demanded.
- **Polymorphic `stockable` morph instead of `product_variant_id` (a shared engine for every purchasable).** Future-proofs non-variant purchasables (marketplace vendor stock). Rejected as *shared storage*: it doubles the morph across `stock_levels` / `stock_movements`, and — fatally — it orphans the indexed `stock_available` rollup on `product_variants` that the storefront filters and sorts by (you would need the rejected separate `StockSummary` table). The set it serves is near-empty anyway: most custom purchasables are not stock-tracked, and those that are (event seats, external WMS) rarely fit `integer on_hand at a location`. The polymorphism that *is* wanted — letting a custom purchasable participate in cart/checkout stock ops — is delivered by the `TracksStock` capability seam (above) without a shared table: the seam is polymorphic, the storage is per-implementation.
- **Drop `Purchasable`, force everything sold to be a `ProductVariant`.** Would make stock trivially uniform. Rejected: Lunar's model extension swaps a *single* class, so this hosts exactly one purchasable type — you lose selling a variant *and* a ticket *and* a subscription as distinct models — and forces non-physical items (gift cards, services, bundles, vendor listings) into a variant shape full of irrelevant fields. The morph's real pain (shipping options masquerading as purchasables) is misuse, addressed by de-morphing shipping options, not by deleting the abstraction.
- **Live-derive `on_hand` from the ledger (no cached column).** Every read becomes an aggregate over a growing ledger. Rejected for the denormalised balance, with `lunar:stock:reconcile` as the safety net.
- **Decrement at order placement (single counter, no location).** Cannot pick a location (none chosen yet) and conflates present with sellable — which is precisely the split `on_hand` vs `committed` makes. Rejected.

## Migration impact

- **Database migrations** (new files in the still-open v2 baseline sequence). Because v2 is unreleased and the variant table is still being assembled, the rollup columns are **born on `create_product_variants_table`** (drop `stock`, keep `backorder`; add the five `stock_*` columns, index `stock_available`) rather than added-then-seeded-then-dropped — a baseline seed step would be a no-op on every fresh install (no variants exist yet). The real backfill lives in the upgrade package.
  - `create_stock_levels_table`
  - `create_stock_movements_table`
  - `create_stock_reservations_table`
  - amend `create_product_variants_table` — drop `stock`, add `stock_on_hand` / `stock_incoming` / `stock_committed` / `stock_reserved` / `stock_unavailable` (index `stock_available`).
- **Breaking changes to the public contract surface:**
  - `Contracts\Actions\Products\AdjustsStock::execute()` gains a `Location` parameter — Rector rule in the `upgrade` package inserting `Location::getDefault()` at existing call sites.
  - `ProductVariant.stock` column / attribute removed; reads route through `getTotalInventory()` / the `stock_*` rollup / `stockLevels`. Direct `->stock` writes break (already discouraged — `AdjustStock` was the sanctioned path).
  - New model contracts `Models\Contracts\{StockLevel, StockMovement, StockReservation}`; the `Contracts\TracksStock` capability (implemented by `ProductVariant`, default trait `HasStock`); and `Contracts\Actions\Products\{RecordsStockMovement, RecomputesStockRollup, CommitsStock, ReleasesStock, HoldsStock, ReleasesHold, ReservesStock, ReleasesReservation, CommitsReservation}` bound in the providers; `stockLevels()` on `Location`.
- **Upgrade path for v1.x consumers:** v1 stores `stock`/`backorder` per variant, no location. The upgrade package, for each variant with `stock <> 0`, creates a `StockLevel` row at `Location::getDefault()` with `on_hand = stock` and an `OpeningBalance` movement, then backfills the rollup — plus the `AdjustStock` Rector rule. One-way, per [[feedback_upgrade_migrations_no_down]].
- **Translation / locale impact:** `StockMovementType` labels and new Filament labels (per-location bucket editor, movement history, reservations) added English-first, then mirrored across the other 15 locales.
- **Filament / admin impact:** core ships **minimal, variant-scoped** inventory controls — and **no standalone inventory screens**, since add-ons are expected to provide opinionated inventory systems. The variant Inventory page shows the selling-policy fields, the `AdjustStock` action, and a read-only rollup summary (on-hand / committed / reserved / available) plus recent movements. Those controls live in one **swappable bridge schema** (extension hooks + subclass-and-rebind), and a `LunarPanel` disable toggle (e.g. `withoutInventoryControls()`, mirroring `excludeResources()`) drops them entirely so an add-on can take over. Per-location editing, the full ledger history and reservation management are add-on concerns. Supersedes the single-line stock action from [[0009-filament-actions-and-global-search]].

## Resolved decisions

- **Stock generalises via a `TracksStock` capability, not a shared table.** `Purchasable` stays the "what can be sold" seam; `TracksStock` is the opt-in, global-first commit/reserve capability the cart/checkout/lifecycle call polymorphically; `ProductVariant` is the only built-in implementation; the location-based ledger stays variant-specific. Custom stock-tracked purchasables implement `TracksStock` with their own storage.
- **Committed automation: full.** Commit-on-place, allocate-on-fulfil, release-on-cancel/ship ship in the fundamentals so `committed` is live and 0025's restock-on-cancel works as a commitment release.
- **Order-cancel releases global commitment; fulfilment-cancel de-allocates the location only.** They are distinct hooks — conflating them breaks the `global.committed >= sum(location.committed)` invariant.
- **Reversals are first-class.** Un-ship and undo-return (reversible per [[0022-order-fulfilments]]) record opposite-signed movements and reverse the commitment move, so stock does not drift on reversal.
- **Allocate at fulfilment creation.** The fulfilment claims the location's stock when it is created; ship then converts committed-on-hand to gone.
- **Reservations: seam now, checkout wiring later.** `StockReservation` + the `ReservesStock`/release/commit seam + `lunar:stock:release-expired` ship as substrate (with optional `expires_at` for auto-release); making a checkout reserve is the follow-on. A reservation converts to a commitment at order placement, so availability never blips.
- **Oversell at placement is allowed by design, surfaced as a warning.** With no reservation taken, the add-to-cart check is advisory and placement may drive `available` negative — mirroring the negative-`on_hand`-on-ship stance (you cannot un-sell a placed order). The `ReservesStock` seam is the supported way to close the window; a configurable deny-oversell mode is a later option (tracked in `TODO.md`).
- **Idempotent counters + canonical predicate.** `committed`/`reserved`/`unavailable` are unledgered counters, so every hook fires exactly once (guarded on state transitions / timestamps), and a single shared predicate defines committed quantity for both the live hooks and `lunar:stock:reconcile`.
- **Rollup-on-variant accepted despite the write-lock.** Order placement write-locks the variant rollup row; the lock is kept to a single narrow `UPDATE` inside the placement transaction (not held across the pipeline). The separate-summary-table alternative was weighed against this and rejected. The narrow-lock rule is informed by Shopify's reservation rebuild: their bottleneck under flash-sale load was DB connections held open elsewhere in checkout, not the counter write itself — so the discipline is to never hold the stock lock across slow steps. A single counter row has a known flash-sale ceiling; the documented escape hatch (out of scope here) is Shopify's row-per-sellable-unit pool with `SKIP LOCKED`, warranted only at that scale.
- **Negative `on_hand` allowed on automatic ship movements** (you cannot un-ship a real parcel), surfaced as a warning; manual adjustments are free-form.
- **Availability is the global sum across all locations** for now. Location/channel-scoped selling is a confirmed future need, deferred to a planned routing follow-on (tracked in `TODO.md`); the per-location buckets here are built to be its substrate so it lands additively.
- **`incoming` is a manual field core stores**, not auto-populated; a purchasing/inventory add-on drives it.

## References

- [[0022-order-fulfilments]] — `Location` model, fulfilment ship/return verbs (incl. reversible un-ship / undo-return), dropped `Backordered`.
- [[0025-order-cancellation]] — restock-on-cancel deferred here.
- [[0028-line-item-refunds]] — refund restock toggle deferred here.
- [[0031-fulfilment-methods]] — `order_lines.requires_fulfilment`, the physical-line predicate committed keys off.
- [[0009-filament-actions-and-global-search]] — `AdjustStock` / `AdjustStockAction` stop-gap, to be superseded.
- `Contracts\Purchasable::getTotalInventory()` / `canBeFulfilledAtQuantity()` — the read seam this preserves.

## Implementation plan

Built on branch `feat/inventory-fundamentals`. The engine ships additive-first; the destructive column drop is split into its own atomic slice so each lands with the app working.

- [x] **Slice 1 — substrate.** `StockLevel`, append-only `StockMovement` ledger, the `stock_*` rollup columns, `RecordStockMovement` + `RecomputeStockRollup`, `HasStock` on `ProductVariant`. Additive — keeps the old `stock` column.
- [x] **Slice 2 — committed automation.** `TracksStock` capability; committed recomputed from the order book (`SyncStockCommitment`, the canonical predicate); lifecycle listeners (placed / cancelled / fulfilment create / ship / un-ship / return / undo-return); `lunar:stock:reconcile`.
- [x] **Cutover.** `getTotalInventory()` reads `stock_available`; drop the `stock` column; `AdjustStock` records a movement; rewire every `->stock` reader across core, table-rate-shipping, admin and filament; migrate tests. _(Split out of slice 1 so the engine goes live atomically.)_
- [x] **Admin messaging.** Hint-icon tooltips on the selling-policy fields, "Selling Policy" label, backorder shown only when the policy consults it. _(Emergent from review; not in the original cut.)_
- [x] **Slice 3 — reservations.** `StockReservation` + `reserveStock` / release / commit seam; `stock_reserved` rollup; `lunar:stock:release-expired` scheduled by default. Substrate only — checkout wiring is a follow-on.
- [x] **Slice 4a — disable toggle.** `LunarPanel::withoutInventoryControls()` hides the built-in per-variant inventory pages so an add-on can supply its own opinionated system.
- [ ] **Slice 4b — control rework.** Consolidate the variant Inventory page into one swappable bridge schema (extension hooks + subclass-and-rebind): selling-policy fields, the `AdjustStock` action as a header action (replacing the editable stock field), a read-only rollup summary (on-hand / available / committed / reserved) and recent movements. No standalone screens; per-location editing, full ledger history and reservation management left to add-ons.
- [ ] **Slice 5 — upgrade package.** Backfill a `StockLevel` + `OpeningBalance` movement from each v1 variant's `stock` on v1 → v2; no `AdjustStock` Rector needed (the new `Location` parameter is nullable-last, so existing call sites stay valid).
