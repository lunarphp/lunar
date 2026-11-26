# 0082 — Selling-policy rework

- Status: accepted
- Author: Glenn Jacobs (with Claude)
- Created: 2026-09-09
- TODO item: Selling-policy rework — declarative model: deny-oversell, sell-against-incoming, continue-selling boolean (follow-on to 0038)

## Problem

`product_variants.selling_policy` is a closed three-value enum (`always` / `in_stock` /
`in_stock_or_on_backorder`, from [[0048-rename-purchasable-to-selling-policy]]) plus a manual
`backorder` integer that only the third mode consults. The default `ResolvesInventory`
action (`Actions/Products/ResolveInventory`, the seam `ProductVariant::getTotalInventory()`
and `canBeFulfilledAtQuantity()` delegate to, from [[0085-product-bundles]]) reads both.
Three things creak:

- **Two disconnected forward-stock numbers.** [[0038-inventory-fundamentals]] made `incoming`
  a real per-location bucket (`StockLevel.incoming`, rolled up to `stock_incoming`) but left
  the backorder policy alone. So the variant carries a merchant-maintained allowance *and* a
  tracked incoming figure, and the selling policy consults the stale-prone manual one. Worse,
  `incoming` has no first-party write path: `RecordStockMovement` is the single write seam for
  the `on_hand` ledger, the panel's `InventoryCard` shows per-location incoming read-only, and
  the Filament bridge's `ProductVariantInventory` does not show it at all. The tracked figure
  sits at 0 in every store without an add-on.
- **No way to hard-stop overselling.** `CartLineStock` checks stock when a line is added or
  updated. Nothing re-checks when the order is created: the default
  `StepList::OrderCreateValidation` steps (`ValidateCartForOrderCreation`,
  `ValidateCustomerForDiscounts`) check addresses, shipping, fulfilment and discount
  eligibility but not stock (`docs/reference/carts.mdx` says otherwise). Concurrent carts can
  jointly drive `available` negative, and a merchant selling strictly limited stock has no
  enforcement, only advice.
- **The enum is closed.** The three values encode two orthogonal decisions (ignore stock
  entirely; count incoming as well as available). Each new combination needs a new enum case
  and every consumer updated. The panel also still labels the field "Purchasable"
  (`products.field_selling_policy`, `products.variant_field_selling_policy`), the pre-0048
  name.

This is the last Ideas item that reshapes a baseline column. Pre-release it is a fold-in
edit; post-release it becomes a schema migration, an enum deprecation, a Rector rule and a
breaking-change cycle.

## Proposal

Replace the enum with the two decisions it encodes, give `incoming` a first-party manual
write path, and check stock again when the order is created.

### Two booleans replace the enum

`product_variants` drops `selling_policy` (and its index) and `backorder` (and its index),
and gains:

```
continue_selling        boolean, default true    sell regardless of stock
sell_against_incoming   boolean, default false   sellable quantity includes stock_incoming
```

- `continue_selling = true` is the out-of-the-box default, matching today's `always` default:
  a fresh store sells without setting up stock. Turning it off makes stock binding.
- The `SellingPolicy` enum is deleted. Value mapping: `always` → `(true, false)`,
  `in_stock` → `(false, false)`, `in_stock_or_on_backorder` → `(false, true)`.
- The default `ResolveInventory` action collapses to:

```php
public function execute(ProductVariant $variant): VariantInventory
{
    return new VariantInventory(
        available: (int) $variant->stock_available
            + ($variant->sell_against_incoming ? (int) $variant->stock_incoming : 0),
        unlimited: $variant->continue_selling,
    );
}
```

`ProductVariant::getTotalInventory()` and `canBeFulfilledAtQuantity()` keep delegating to
`ResolvesInventory`, and `VariantInventory` keeps its shape, so the `Purchasable` contract,
`CartLineStock`, `ShippingOption` and the bundles decorator (`ResolveBundleInventory`, which
derives a bundle's figure from its components' `VariantInventory`) are unaffected.

The backorder allowance is gone: selling beyond available stock now means selling against
the tracked `stock_incoming` figure, one source of truth for forward stock.

Other readers of the enum move to the booleans:

- `GenerateProductVariants` copies `continue_selling` and `sell_against_incoming` from the
  template variant instead of `selling_policy`.
- The panel's `LowStockWidget` lists variants with `continue_selling = false`.
- The panel's `TasksWidget` out-of-stock count covers variants with `continue_selling = false`
  whose sellable quantity (`stock_available`, plus `stock_incoming` when
  `sell_against_incoming`) is at most 0. Today it counts only `in_stock` variants, so a
  backorder variant with no allowance left is silently missed.

### `incoming` becomes hand-editable

Without a write path, `sell_against_incoming` would be a dead toggle in stock-core Lunar
(incoming permanently 0, a capability regression from the editable `backorder` field). So
core gains a minimal manual editor, per 0038's minimal-controls stance: a field, not a
purchasing system.

- **New action + contract**: `Actions/Products/SetIncomingStock` implementing
  `Contracts\Actions\Products\SetsIncomingStock`, registered in `ActionServiceProvider`.
  `execute(ProductVariant $variant, Location $location, int $quantity, ?string $note = null): StockLevel`
  sets the level's `incoming` **absolutely** (it is a statement of expectation, not a ledger
  delta), inside a transaction with the same `firstOrCreate` + `lockForUpdate` discipline as
  `RecordStockMovement`, then recomputes the rollup via `RecomputesStockRollup`. A negative
  quantity is rejected. No `StockMovement` row: incoming is not part of the `on_hand` ledger.
  When goods arrive the merchant records the arrival with the existing adjust-stock action and
  lowers `incoming` accordingly; automating that pairing is add-on territory.
- **Panel**: the `InventoryCard` per-location incoming cell becomes editable inline, like the
  on-hand cell. It posts to a new route on `ProductVariantStockController`, which writes
  through `SetsIncomingStock`.
- **Filament bridge**: `ProductVariantInventory` manages the default location only and never
  edits inline. Its rollup section gains a `stock_incoming` placeholder and a
  `SetIncomingStockAction` header action next to `AdjustStockAction`, both against
  `Location::getDefault()`.

**Single-writer seam.** A purchasing add-on that derives `incoming` from purchase orders must
be able to switch manual entry off (one bucket, one writer) while keeping core's other
inventory controls. The incoming editor is therefore individually disableable in both admins,
named after the existing `withoutInventoryControls()` / `usesInventoryControls()` pair on the
admin's `LunarPanelManager`:

- Filament: `LunarPanel::withoutIncomingStockEntry()` / `usesIncomingStockEntry()`. When off,
  the header action is hidden; the rollup placeholder stays.
- Panel: `Panel::withoutIncomingStockEntry()` / `usesIncomingStockEntry()` on `PanelManager`,
  next to its other fluent registration methods. When off, the incoming cell renders
  read-only and the panel's incoming route refuses writes with a 403, so the editor and its
  endpoint cannot disagree.

Add-ons write through the same `SetsIncomingStock` action either way. Disabling entry turns
off the admin editors only, never the action.

### Deny-oversell: stock is re-checked when the order is created

There is no advisory/deny toggle. `continue_selling` already expresses the merchant's intent
per variant: `true` means oversell freely, `false` means stock is binding. When it is false,
order creation enforces it.

- A new validator, `Validation\Cart\ValidateCartStock`, joins the
  `StepList::OrderCreateValidation` defaults after the existing steps. It runs in
  `Cart::createOrder()` against the freshly recalculated cart, before `CreatesOrder` writes
  anything, and in `Cart::canCreateOrder()`, so a storefront can check before it takes
  payment.
- It sums line quantities per purchasable (two lines of the same variant with different
  `meta` share stock) and asks `canBeFulfilledAtQuantity()` for the total, against a freshly
  loaded purchasable rather than the cart's cached relation.
- On failure it throws `Exceptions\Carts\InsufficientStockException`, a `CartException`
  subclass that also exposes the offending cart lines. Every caller that already handles
  `CartException`, including both payment drivers, keeps working unchanged.
- Like every step list, stores can swap or remove the validator through the `Steps` facade.

**Where the check sits relative to payment.** The check runs where the order is first
created:

- **Draft-order checkout (documented flow).** The storefront calls `createOrder()` to create
  a draft order, then takes payment against it. The check runs before any money moves, and a
  failure is an ordinary checkout error. The Stripe and PayPal drivers then place the
  existing draft without calling `createOrder()` again, so no stock check runs between
  payment and placement.
- **Driver-created orders.** When no draft exists, `StripePaymentType::authorize()` and
  `PaypalPaymentType::authorize()` call `createOrder()` themselves, after the customer has
  paid (Stripe's automatic policy captures on confirmation; PayPal's order is `COMPLETED`).
  A stock failure here is a paid cart with no order. Both drivers already reach that branch
  for any `CartException`, so this spec defines what they do there:
  - Stripe, uncaptured intent (`requires_capture`, the manual policy): cancel the intent to
    release the hold, then return the failed `PaymentAuthorize`. Today the hold is left
    until it expires.
  - Stripe, captured intent: unchanged. The driver marks the intent processed and dispatches
    `OrphanedPaymentIntentDetected`, the existing "paid, no order" signal.
  - PayPal: dispatch a matching `OrphanedPaypalOrderDetected` event, carrying the PayPal
    order ID, cart ID and message, before returning the failure. Today the driver returns
    the failure without any signal.

  Neither driver refunds automatically: whether to refund, back-order or contact the
  customer is the merchant's decision, made from the orphan event.
- **Not re-checked at placement.** When a driver places an existing draft, payment has
  already been taken, so refusing placement could only strand money. The remaining window,
  from draft creation to placement, and concurrent placements racing for the last unit, are
  closed by the checkout-reservations follow-on. The `ReservesStock` seam and
  `StockReservation` model already exist for it; this spec does not implement
  reservation-at-checkout.

The `PaymentType` reference docs state the contract for third-party drivers: an order created
inside `authorize()` can fail with `InsufficientStockException`, after payment, and the
driver must release any uncaptured authorization and report captured funds.

### Admin form changes

Both variant forms replace the three-option selector and backorder quantity with two toggles:

- "Continue selling when out of stock" (`continue_selling`).
- "Sell against incoming stock" (`sell_against_incoming`), shown only when continue-selling is
  off (when stock is ignored entirely, incoming is irrelevant). This replaces the Filament
  bridge's `disabled unless in_stock_or_on_backorder` rule on the backorder field.

In the panel, `InventoryCard` loses its segmented selling-policy control and backorder
quantity field, and `VariantFields` swaps `selling_policy` / `backorder` for the two booleans.
This retires the panel's stale "Purchasable" label.

### Factories

`ProductVariantFactory` defaults to `(continue_selling: true, sell_against_incoming: false)`;
states `stockBound()` and `sellsAgainstIncoming()` cover the other combinations so tests never
hand-set the booleans. (`inStock()` already seeds on-hand stock, so it keeps that meaning.)

## Alternatives considered

- **Keep the enum, add cases.** The two decisions produce four combinations today and more
  with every future dimension; a closed set forces a new case plus every consumer updated per
  combination. Rejected: the enum is the wrong shape, not merely incomplete.
- **Keep a manual backorder allowance alongside `incoming`.** Preserves a hand-set cap but
  keeps two forward-stock numbers that drift apart, the exact problem. Rejected.
- **A cap on incoming-selling** (sell only *n* of the inbound quantity). Real need for some
  merchants, but it reintroduces a second number; a purchasing add-on that owns `incoming` can
  model caps properly (per PO, per window). Rejected for core.
- **Deny-oversell as store-level config or a third per-variant boolean.** A config switch
  makes enforcement a value, but it is behaviour that belongs to the variant's own
  declaration; a third boolean conflates merchandising intent with enforcement.
  `continue_selling = false` already *is* the merchant saying "stock is binding". Rejected.
- **Re-check at placement (when `placed_at` is set) as well.** Every first-party driver
  places after taking payment, so a refusal there turns every late stock-out into a paid,
  unplaced order. Rejected; reservations close that window properly.
- **Fold the check into `ValidateCartForOrderCreation`.** Fewer classes, but a store that
  wants advisory behaviour would have to replace the whole order-creation validator. A
  separate step can be removed on its own. Rejected.
- **Refund automatically when a driver-created order fails the check.** Refunds carry fees
  and are a merchant policy decision; the orphan events already hand that decision over.
  Rejected.
- **Do nothing.** Ships v2.0 with a closed enum, a dead `incoming` bucket, a stale-prone
  allowance and no enforcement, and turns this baseline edit into a post-release breaking
  change. Rejected.

## Migration impact

- **Database**: baseline edit to `create_product_variants_table`: drop `selling_policy` and
  `backorder` with their indexes, add the two booleans. No new tables.
- **Breaking changes**: `Enums\SellingPolicy` is removed; the `ProductVariant` property
  surface changes (`$selling_policy` / `$backorder` → the booleans). `Purchasable`,
  `TracksStock`, `ResolvesInventory` and `VariantInventory` are unchanged. Order creation can
  now throw `InsufficientStockException` (a `CartException`). Pre-release, so baseline +
  Rector, no deprecation cycle.
- **Upgrade path (v1.x, `packages/upgrade`)**: the `rename_purchasable_to_selling_policy`
  step becomes `purchasable_to_selling_booleans`. It reads the v1 `purchasable` column (or
  `selling_policy`, where the earlier step already ran), normalises the
  `in_stock_or_backorder` typo as today, and maps to the booleans as above; unrecognised
  values map to `(true, false)`, matching the current `always` fallback.
  - Where the v1 mode was `in_stock_or_on_backorder` and `backorder > 0`, the step adds
    `backorder` to the default location's `StockLevel.incoming` and recomputes the rollup,
    so those variants sell the same quantity after the upgrade as before. The upgrade guide
    tells merchants these incoming figures came from their backorder allowances and should
    be reviewed.
  - It then drops `selling_policy`/`purchasable` and `backorder`.
  - It runs after `backfill_stock_from_variants`, which creates the stock levels it writes.
  - Rector: `LunarSetList` drops the `purchasable` → `selling_policy` property rename and
    adds a rule that rewrites equality checks of `$variant->purchasable` /
    `$variant->selling_policy` against the three values (string or enum case) into the
    boolean expressions. Other uses are listed in the upgrade guide for manual changes.
- **Translations**: retire the `selling_policy*` / `backorder*` keys and add the two toggle
  labels and help texts, the incoming-editor strings and the insufficient-stock exception
  message in `core`, `panel`, `filament` and `admin`: English first, translated across all
  16 locales.
- **Filament / admin impact**: `ProductVariantInventory` and `ProductVariantForm` (bridge),
  `ManageProductInventory` (admin resources), `LunarPanelManager` (new toggle).
- **Panel impact**: `InventoryCard`, `VariantFields`, `ProductVariantController`,
  `ProductEditController`, `ProductVariantStockController`, `LowStockWidget`, `TasksWidget`,
  `PanelManager` (new toggle).
- **Payment drivers**: Stripe cancels an uncaptured intent when a driver-created order fails;
  PayPal gains `OrphanedPaypalOrderDetected`.
- **Docs**: `reference/inventory`, `reference/products`, `reference/carts` (including the
  order-creation validator list), `extending/carts`, the Stripe and PayPal add-on pages, the
  product display and listing guides, and the upgrade guide.

## Open questions

None.

## References

- [[0038-inventory-fundamentals]]: the stock buckets, minimal-controls stance, the
  oversell-by-design note and the add-on takeover toggle this spec builds on.
- [[0048-rename-purchasable-to-selling-policy]]: the rename that scoped this rework out as
  "a separate post-alpha item"; this spec is that item, pulled pre-release.
- [[0085-product-bundles]]: the `ResolvesInventory` seam the new logic lives behind.
- [[0093-registrable-calculation-steps]]: the `StepList` registration the new validator joins.
- `Contracts/TracksStock.php`, `Actions/Products/ReserveStock.php`: the reservation seam the
  deny-oversell stance defers to.
- `Actions/Products/RecordStockMovement.php`: the locking discipline `SetIncomingStock`
  mirrors.
- `StripePaymentType::authorize()`, `PaypalPaymentType::authorize()`: the driver-created
  order paths.
- TODO Ideas: checkout stock reservations, location-scoped availability: unchanged
  follow-ons; both read the same buckets this spec keys selling off.

## Implementation plan

Each slice leaves `2.x` working, so the slice that removes the enum also moves every reader of
it, including both admins' forms, to the booleans.

- [ ] Slice 1 — core schema + model: booleans on the baseline, enum removal,
  `ResolveInventory` rework, `GenerateProductVariants`, factory states; the panel's
  `InventoryCard` toggles, `VariantFields` and dashboard widgets; the Filament bridge and admin
  inventory forms; lang keys across 16 locales; reference docs and the alpha upgrade guide.
- [ ] Slice 2 — `SetIncomingStock` action + contract, `ActionServiceProvider` registration,
  `setIncomingStock()` verb on the variant, tests.
- [ ] Slice 3 — `ValidateCartStock` + `InsufficientStockException` in
  `OrderCreateValidation`, Stripe intent cancellation and PayPal orphan event on
  driver-created order failure, tests, docs.
- [ ] Slice 4 — panel: inline incoming editor and route, disable toggle, lang keys across 16
  locales.
- [ ] Slice 5 — Filament bridge + admin resources: incoming rollup + header action, disable
  toggle, lang keys across 16 locales.
- [ ] Slice 6 — upgrade package: mode → booleans data step, incoming seed, column drops,
  Rector rule, upgrade guide.
