# 0082 — Selling-policy rework

- Status: draft
- Author: Glenn Jacobs (with Claude)
- Created: 2026-09-09
- TODO item: Selling-policy rework — declarative model: deny-oversell, sell-against-incoming, continue-selling boolean (follow-on to 0038)

## Problem

`product_variants.selling_policy` is a closed three-value enum (`always` / `in_stock` /
`in_stock_or_on_backorder`, from [[0048-rename-purchasable-to-selling-policy]]) plus a manual
`backorder` integer that only the third mode consults. Three things creak:

- **Two disconnected forward-stock numbers.** [[0038-inventory-fundamentals]] made `incoming`
  a real per-location bucket (`StockLevel.incoming`, rolled up to `stock_incoming`) but
  deliberately left the backorder policy alone: "Selling against it is a backorder policy
  (`backorder` column + `purchasable` mode), unchanged by this spec." So the variant now
  carries a merchant-maintained allowance *and* a tracked incoming figure, and the selling
  policy consults the stale-prone manual one. Worse, `incoming` has no first-party write
  path — `RecordStockMovement` is "the single write seam for the `on_hand` ledger", the
  panel's `InventoryCard` and the Filament bridge's `ProductVariantInventory` both show
  incoming read-only — so the tracked figure sits at 0 in every store without an add-on.
- **No way to hard-stop overselling.** 0038 records that oversell at placement is allowed by
  design: `CartLineStock` validates at add-to-cart/update time, nothing re-checks at
  placement, so concurrent carts can jointly drive `available` negative. The "configurable
  deny-oversell mode" was tracked as a later option. A merchant selling strictly limited
  stock has no enforcement, only advice.
- **The enum is closed.** The three values encode two orthogonal decisions (ignore stock
  entirely; consult incoming as well as available). Each new combination needs a new enum
  case and every consumer updated. The panel also still labels the field "Purchasable" — the
  pre-0048 name.

This is the last Ideas item that reshapes a baseline column. Pre-release it is a fold-in
edit; post-release it becomes a schema migration, an enum deprecation, a Rector rule and a
breaking-change cycle. Decide now or live with the enum.

## Proposal

Replace the enum with the two decisions it encodes, give `incoming` a first-party manual
write path, and make the stock check enforced at placement.

### Two booleans replace the enum

`product_variants` drops `selling_policy` and `backorder`, gains:

```
continue_selling        boolean, default true    sell regardless of stock
sell_against_incoming   boolean, default false   sellable quantity includes stock_incoming
```

- `continue_selling = true` is the out-of-the-box default, matching today's `always` default —
  a fresh store sells without setting up stock. Turning it off makes stock binding.
- The `SellingPolicy` enum is deleted. Value mapping: `always` → `(true, false)`,
  `in_stock` → `(false, false)`, `in_stock_or_on_backorder` → `(false, true)`.
- `ProductVariant` logic collapses to:

```php
public function getTotalInventory(): int
{
    return $this->stock_available
        + ($this->sell_against_incoming ? $this->stock_incoming : 0);
}

public function canBeFulfilledAtQuantity(int $quantity): bool
{
    return $this->continue_selling || $quantity <= $this->getTotalInventory();
}
```

The `Purchasable` contract is untouched — `canBeFulfilledAtQuantity()` keeps its signature,
so `CartLineStock`, `ShippingOption` and custom purchasables are unaffected at the seam.

The backorder allowance is gone: selling beyond available stock now means selling against
the tracked `stock_incoming` figure, one source of truth for forward stock.

### `incoming` becomes hand-editable

Without a write path, `sell_against_incoming` would be a dead toggle in stock-core Lunar
(incoming permanently 0 — a capability regression from the editable `backorder` field). So
core gains a minimal manual editor, per 0038's minimal-controls stance: a field, not a
purchasing system.

- **New action + contract**: `Actions/Products/SetIncomingStock` implementing
  `Contracts\Actions\Products\SetsIncomingStock`, registered in `ActionServiceProvider`.
  `execute(ProductVariant $variant, Location $location, int $quantity, ?string $note = null): StockLevel`
  — sets the level's `incoming` **absolutely** (it is a statement of expectation, not a
  ledger delta), inside a transaction with the same `firstOrCreate` + `lockForUpdate`
  discipline as `RecordStockMovement`, then recomputes the rollup via
  `RecomputesStockRollup`. No `StockMovement` row — incoming is not part of the `on_hand`
  ledger. When goods arrive the merchant records the arrival with the existing adjust-stock
  action and lowers `incoming` accordingly; automating that pairing is add-on territory.
- **Panel**: the `InventoryCard` per-location incoming cell becomes editable (inline, next to
  the adjust-stock affordance), writing through the new action.
- **Filament bridge**: a matching control in `ProductVariantInventory`.

**Single-writer seam.** A purchasing add-on that derives `incoming` from purchase orders
must be able to switch the manual entry off — one bucket, one writer — while keeping core's
other inventory controls (a purchasing-only add-on is not a full inventory takeover). The
incoming editor is therefore individually disableable in both admins, alongside the existing
whole-surface takeover from 0038:

- Filament: a toggle on the bridge configuration (naming per the existing
  `withoutInventoryControls()` pattern, e.g. `withoutIncomingStockEntry()`).
- Panel: the equivalent extension point on the panel's configuration surface.

Core UI and add-ons share the `SetsIncomingStock` seam either way — disabling entry is
purely a UI concern, never a behavioural fork.

### Deny-oversell: the stock check is re-run at placement

There is no advisory/deny toggle. `continue_selling` already expresses the merchant's
intent per variant: `true` means oversell freely, `false` means stock is binding — so when
it is false, placement enforces it.

- Order creation re-validates every stock-tracked line (`canBeFulfilledAtQuantity()` against
  fresh data) as the first validation step of the create-order pipeline, before any order
  rows are written. A failing line aborts placement with a typed exception carrying the
  offending line(s), which the storefront surfaces the same way it surfaces any other
  placement failure.
- This shrinks the oversell window from "add-to-cart → placement" to the placement
  transaction itself. Full closure (concurrent placements racing the same last unit) is the
  checkout-reservations follow-on — the `TracksStock::reserveStock()` seam and
  `StockReservation` model already exist for it; this spec does not implement first-party
  reservation-at-checkout.

### Admin form changes

Both variant forms replace the three-option select + backorder quantity with two toggles:

- "Continue selling when out of stock" (`continue_selling`).
- "Sell against incoming stock" (`sell_against_incoming`), shown/enabled only when
  continue-selling is off (when stock is ignored entirely, incoming is irrelevant) —
  replacing the current `disabled unless in_stock_or_on_backorder` dance on the backorder
  field.

This also retires the panel's stale "Purchasable" field label.

### Factories

`ProductVariantFactory` defaults to `(continue_selling: true, sell_against_incoming: false)`;
states `inStock()` and `sellsAgainstIncoming()` cover the other combinations so tests never
hand-set the booleans.

## Alternatives considered

- **Keep the enum, add cases.** The two decisions produce four combinations today and more
  with every future dimension; a closed set forces a new case plus every consumer updated
  per combination. Rejected — the enum is the wrong shape, not merely incomplete.
- **Keep a manual backorder allowance alongside `incoming`.** Preserves a hand-set cap but
  keeps two forward-stock numbers that drift apart — the exact problem. Rejected.
- **A cap on incoming-selling** (sell only *n* of the inbound quantity). Real need for some
  merchants, but it reintroduces a second number; a purchasing add-on that owns `incoming`
  can model caps properly (per-PO, per-window). Rejected for core.
- **Deny-oversell as store-level config or a third per-variant boolean.** A config switch
  makes enforcement a value (`config` is for values, but this is behaviour that belongs to
  the variant's own declaration); a third boolean conflates merchandising intent with
  enforcement. `continue_selling = false` already *is* the merchant saying "stock is
  binding". Rejected.
- **Do nothing.** Ships v2.0 with a closed enum, a dead `incoming` bucket, a stale-prone
  allowance and no enforcement — and converts this spec's baseline edit into a post-release
  breaking change. Rejected.

## Migration impact

- **Database**: baseline edit to `create_product_variants_table` — drop `selling_policy`
  (and its index) and `backorder`, add the two booleans. No new tables.
- **Breaking changes**: `Enums\SellingPolicy` is removed; `ProductVariant` property surface
  changes (`$selling_policy`/`$backorder` → the booleans). `Purchasable` and `TracksStock`
  contracts unchanged. Pre-release, so baseline + Rector, no deprecation cycle.
- **Upgrade path (v1.x, `packages/upgrade`)**: the existing
  `rename_purchasable_to_selling_policy` step reworks into purchasable-mode → booleans
  (mapping above, rogue values → `(true, false)` matching its current `always` fallback).
  Where the v1 mode consulted the allowance (`in_stock_or_on_backorder`) and `backorder > 0`,
  seed the default location's `StockLevel.incoming` from it (one-time courtesy so
  backorder-selling keeps working on day one), recompute rollups, then drop `backorder`.
  Rector: property/enum rewrites added to `LunarSetList`.
- **Translations**: retire the `selling_policy.*` / `backorder.*` keys and add the two
  toggle labels + tooltips and the incoming-editor strings in `panel`, `filament` and
  `admin` — English first, translated across all 16 locales.
- **Filament / admin impact**: `ProductVariantInventory` and `ProductVariantForm` (bridge),
  `ManageProductInventory` (admin resources), `VariantFields` + `InventoryCard` +
  `ProductVariantController` (panel), plus the two new disable seams.

## Open questions

1. **Where exactly the placement re-check sits relative to payment authorization.** Payment
   drivers authorize before placing the order; a deny after auth means the storefront must
   void/release. Proposed resolution: the re-check runs in the create-order pipeline's
   validation phase, and drivers already surface order-creation failure to the storefront —
   verify the Stripe driver's failure path handles it cleanly and document the expectation
   on `PaymentType` implementers. Owner: implementation slice 3.
2. **Seeding `incoming` from v1 `backorder`.** Proposed above as a one-time courtesy;
   alternative is dropping the number and release-noting that backorder-selling needs
   `incoming` populated. Decide at review.
3. **Exact names of the two disable seams.** Proposed `withoutIncomingStockEntry()` on the
   Filament bridge and the panel equivalent; settle naming against the existing extension
   surfaces during implementation review.

## References

- [[0038-inventory-fundamentals]] — the stock buckets, minimal-controls stance, the
  oversell-by-design note and the add-on takeover toggle this spec builds on.
- [[0048-rename-purchasable-to-selling-policy]] — the rename that scoped this rework out as
  "a separate post-alpha item"; this spec is that item, pulled pre-release.
- `Contracts/TracksStock.php`, `Actions/Products/ReserveStock.php` — the reservation seam
  the deny-oversell stance defers to.
- `Actions/Products/RecordStockMovement.php` — the locking discipline `SetIncomingStock`
  mirrors.
- TODO Ideas: checkout stock reservations, location-scoped availability — unchanged
  follow-ons; both read the same buckets this spec keys selling off.

## Implementation plan

- [ ] Slice 1 — core schema + model: booleans on the baseline, enum removal,
  `getTotalInventory()` / `canBeFulfilledAtQuantity()` rework, factory states, tests.
- [ ] Slice 2 — `SetIncomingStock` action + contract, `ActionServiceProvider` registration,
  tests.
- [ ] Slice 3 — placement-time re-validation in the create-order pipeline + typed failure,
  tests (resolves open question 1).
- [ ] Slice 4 — panel: toggles, inline incoming editor, disable seam, lang keys across 16
  locales.
- [ ] Slice 5 — Filament bridge + admin resources: same surface, disable seam, lang keys
  across 16 locales.
- [ ] Slice 6 — upgrade package: mode → booleans data step, incoming seed, `backorder`
  drop, Rector rules.
