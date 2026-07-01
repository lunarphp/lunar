# 0045 — Optional order-line purchasables and de-morphing shipping options

- Status: proposed
- Author: Glenn Jacobs
- Created: 2026-06-30
- TODO item: Make order line purchasables optional + Stop storing shipping options as polymorphic purchasables

## Problem

Every order line carries a **mandatory** `purchasable` morph (`purchasable_type` / `purchasable_id`), enforced at write time by `OrderLineObserver::assertPurchasable()` — it throws `NonPurchasableItemException` unless `purchasable_type` resolves to a class implementing `Purchasable`. This is wrong for two distinct line kinds:

1. **Shipping lines are a fake morph.** `ShippingOption` is a non-Eloquent value object from the `ShippingManifest` (it implements `Purchasable` so it can masquerade as one). It has no table and no rows. To satisfy the mandatory morph, `CreateShippingLine` hardcodes a placeholder:

   ```php
   // CreateShippingLine.php:34-35
   'purchasable_type' => ShippingOption::class,
   'purchasable_id'   => 1,            // there is no row 1 — there is no table
   ```

   The `purchasable()` relation on a shipping line resolves to nothing usable; `$line->purchasable` is a lie. The line already snapshots everything it needs — `type = 'shipping'`, `identifier`, `description`, `option`, `unit_price`, `unit_quantity`, `meta` (incl. the `collect` flag) — so the morph carries no information the snapshot columns do not already hold.

2. **Ad-hoc charge lines have no purchasable.** Per the lean money model, extra charges are order lines, not a generic fee primitive. A surcharge, a handling fee, or a manual adjustment line — created directly on the order, like `CreateShippingLine` creates the shipping line — has nothing to morph to, yet today it cannot be persisted without a `Purchasable` to point at.

Both kinds are **order-side**: shipping is materialised as an order line at order creation (never a cart line — in the cart it lives on `cart_addresses.shipping_option` as an identifier), and ad-hoc charges are added directly to the order. The cart never needs a purchasable-less line, so this change is scoped to `order_lines`.

## Proposal

Make the **order**-line purchasable **optional**, and make shipping lines **self-describing** rather than morph-backed. Order lines already carry the full snapshot column set ([[0022-order-fulfilments]] / [[0030-fulfillable-order-lines]]), so a purchasable-less line stands on its own.

### Schema — nullable morph on `order_lines` only

`purchasable_type` / `purchasable_id` become **nullable** on `order_lines` (the v2 baseline is still open, so this is an edit to `create_order_lines_table`, not an alter):

```php
$table->nullableMorphs('purchasable');   // was $table->morphs('purchasable');
```

A line with a null morph is **self-describing** — it carries its own `type`, `description`, `unit_price`, the `requires_shipping` / `requires_fulfilment` flags, tax/total columns and `meta`. Those columns are already `NOT NULL` (or defaulted), so the database still guarantees a purchasable-less line describes itself; only the morph becomes optional.

**`cart_lines` is unchanged.** It keeps the mandatory morph and `CartLineObserver` as-is, because core never creates a purchasable-less cart line. Consumer-defined ad-hoc *cart* lines (which would need new snapshot columns on `cart_lines` and a builder seam) are a separate concern, out of scope here.

### Shipping lines stop morphing

`CreateShippingLine` drops the placeholder morph and keys its existing-line lookup on `type` + `identifier`:

```php
$shippingLine = $order->lines->first(fn ($line) =>
    $line->type === 'shipping' && $line->identifier === $shippingOption->getIdentifier()
) ?: App::make(OrderLine::class);

$shippingLine->fill([
    'order_id'             => $order->id,
    'purchasable_type'     => null,        // was ShippingOption::class
    'purchasable_id'       => null,        // was 1
    'type'                 => 'shipping',
    'requires_shipping'    => false,
    'requires_fulfilment'  => false,
    'description'          => $shippingOption->getName(),
    'option'               => $shippingOption->getOption(),
    'identifier'           => $shippingOption->getIdentifier(),
    // unit_price / totals / tax_breakdown / meta (incl. `collect`) unchanged
])->save();
```

Nothing else about the shipping line changes — `type = 'shipping'` already drives `Order::shippingLines()`, the `collect` flag in `meta` already drives the `collection` fulfilment method ([[0031-fulfilment-methods]]), and `requires_fulfilment = false` already keeps shipping lines out of the inventory committed predicate ([[0038-inventory-fundamentals]]).

### The order-line snapshot is authoritative

Once an order exists, its lines are the record — historical shipping/charge detail must not shift if the merchant later edits a rate. There is therefore **no** post-order re-resolution of a live `ShippingOption` from a line: the snapshot columns are read directly (`$line->description`, `$line->unit_price`, `$line->meta['collect']`, …). Cart-time resolution is unchanged — the chosen option is held as an identifier on `cart_addresses.shipping_option` and resolved by the existing `ShippingManifest::getShippingOption(Cart)`. No new `ShippingManifest` or `OrderLine` surface is added.

### Relaxing the observer

`OrderLineObserver::assertPurchasable()` asserts `Purchasable`-ness **only when a morph is set**:

```php
protected function assertPurchasable(OrderLine $orderLine): void
{
    if ($orderLine->purchasable_type === null) {
        return;   // self-describing line (shipping, ad-hoc charge) — no morph to validate
    }
    // ...existing class-implements-Purchasable check unchanged
}
```

A null morph is valid; a *set* morph must still implement `Purchasable`. `CartLineObserver` is untouched.

### Order-line reader audit

A handful of sites read `$orderLine->purchasable`; under the order-lines-only scope this audit is small, and most are already null-tolerant:

- `Listeners\ApplyStockForFulfilmentTransition` — uses `$line->orderLine?->purchasable` (null-safe) and the inventory hooks already gate on `TracksStock`, skipping non-variant lines. No change.
- `Listeners\Concerns\SyncsTrackedStock` — maps `$line->purchasable` then filters to `TracksStock` instances, so null is filtered out. No change.
- Admin `OrderResource` `OrderItemsTable` — already uses `$record->purchasable?->...` for the thumbnail / options / inventory columns, but the product-link column dereferences `$record->purchasable->product_id` unguarded. It gains a null/visibility guard so shipping and ad-hoc lines render without a product link.

`CreateOrderLines` (cart -> order line mapping) is **unchanged**: it only maps cart lines, which always have a purchasable.

## Alternatives considered

- **Make `ShippingOption` a real Eloquent model.** Gives the morph something to point at, but shipping options are computed per-cart by the shipping modifier pipeline ([[0024-shipping-carriers]]) — they are derived values, not stored rows. Persisting them duplicates the rate tables and still leaves ad-hoc charge lines without a purchasable. Rejected.
- **A singleton placeholder `Purchasable` row per pseudo-type.** Keeps the morph non-null by seeding one real row. Rejected — it institutionalises the lie, needs a seeder in the baseline, and does nothing for ad-hoc lines.
- **A dedicated `shipping_lines` table separate from `order_lines`.** Splits shipping out of the line model entirely. Rejected — totals, tax breakdown and refunds ([[0028-line-item-refunds]]) all operate over the unified line set; a parallel table fragments every rollup. `type = 'shipping'` on one table is the established seam.
- **Make `cart_lines` nullable too, for symmetry.** Rejected — core never produces a purchasable-less cart line (shipping is order-side; ad-hoc charges are added to the order). Making `cart_lines` nullable would force defensive null-guards across the entire cart pricing/discount/tax pipeline (~8 sites) to protect a path core cannot reach, and would half-build consumer ad-hoc cart lines without the snapshot columns they need. The full ad-hoc-cart-line feature (cart-line snapshot columns + builder seam + guards) is left to a future spec that does it properly; this spec keeps the cart untouched.
- **Add a `findOption(identifier)` resolution seam / `OrderLine::shippingOption()` accessor.** Rejected — no caller needs it, and resolution would be partial and misleading (options are computed per-cart, so a cart-less lookup finds only statically-registered options). The order-line snapshot is the authoritative post-order record; cart-time resolution already exists via `getShippingOption(Cart)`.

## Migration impact

- **Database** (baseline editable, v2 pre-release): `order_lines` `purchasable` morph -> `nullableMorphs`. No new columns. `cart_lines` unchanged.
- **Breaking changes to the public contract surface:**
  - `$orderLine->purchasable` may now be `null` for shipping and ad-hoc lines. Any consumer code doing `$line->purchasable->...` on a shipping line breaks — but that already returned a non-resolvable morph (`purchasable_id => 1`, no table), so no correct consumer relied on it. Display/price reads route through the snapshot columns (`$line->description`, `$line->unit_price`, …), unchanged.
  - `OrderLineObserver` permits a null morph (additive — previously-valid lines stay valid).
  - No new `ShippingManifest` / `OrderLine` surface.
- **Upgrade path for v1.x consumers:** v1 stores the same fake shipping morph (`purchasable_id => 1`, `purchasable_type => ShippingOption::class`). The upgrade package nulls `purchasable_type` / `purchasable_id` on every `order_lines` row where `type = 'shipping'`, leaving the snapshot columns intact. One-way (the upgrade data migrations are not reversible; restore from backup to undo). No Rector rule needed (the change is data-only; the `->purchasable` accessor still exists, it just returns null for these lines).
- **Translation / locale impact:** none — no new user-facing strings (shipping lines already render from `description`).
- **Filament / admin impact:** the `OrderItemsTable` product-link column gains a null/visibility guard (see reader audit); other line columns are already null-safe.

## Resolved decisions

- **Order-lines only.** Only `order_lines.purchasable` becomes nullable; `cart_lines`, `CartLineObserver`, `CreateOrderLines` and the entire cart pricing/discount/tax pipeline are untouched, because core never produces a purchasable-less cart line. Consumer ad-hoc *cart* lines (cart-line snapshot columns + builder seam) are a separate future spec.
- **No re-resolution seam.** `findOption` / `OrderLine::shippingOption()` are not added. The order-line snapshot is authoritative post-order; cart-time resolution stays on the existing `getShippingOption(Cart)`. Avoids speculative and partially-correct surface.
- **Snapshot columns guarantee self-description.** A purchasable-less order line is valid because its `type` / `description` / `unit_price` / `requires_*` columns are `NOT NULL` (or defaulted); the observer only stops validating the (now absent) morph.

## References

- `Lunar\Core\Pipelines\Order\Creation\CreateShippingLine` — the placeholder morph (`purchasable_id => 1`).
- `Lunar\Core\DataTypes\ShippingOption` — the non-Eloquent `Purchasable` value object.
- `Lunar\Core\Manifests\ShippingManifest` / `Contracts\ShippingManifest` — cart-time, identifier-keyed option resolution (`getShippingOption(Cart)`), unchanged.
- `Lunar\Core\Observers\OrderLineObserver` — the mandatory-morph enforcement being relaxed.
- [[0030-fulfillable-order-lines]] — `requires_fulfilment` decoupled fulfilment from the line `type` string.
- [[0031-fulfilment-methods]] — the `collect` flag in shipping-line `meta`.
- [[0038-inventory-fundamentals]] — the committed predicate skips non-variant / non-fulfilment lines.
- Lean core money model — extra charges are order lines, not a generic fee primitive; this is why ad-hoc charge lines need an optional purchasable.

## Implementation plan

- [x] Slice 1 — nullable morph + relaxed observer. `nullableMorphs` on `order_lines`; `OrderLineObserver::assertPurchasable()` early-returns on null `purchasable_type`. Additive permission — the app still creates the fake shipping morph at this point.
- [x] Slice 2 — de-morph shipping. `CreateShippingLine` stops setting the morph and keys its lookup on `type` + `identifier`; the admin `OrderItemsTable` product-link column already guards null purchasable (`$record->purchasable && …`); migrate tests (`CreateShippingLineTest`, `OrderLineTest`, `CleanUpOrderLinesTest`).
- [x] Slice 3 — upgrade package. Data migration nulling the shipping morph on existing `order_lines` (`type = 'shipping'`); guarded/idempotent, no `down()`.
