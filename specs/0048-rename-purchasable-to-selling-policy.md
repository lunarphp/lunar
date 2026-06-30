# 0048 — Rename `product_variants.purchasable` to `selling_policy`

- Status: proposed
- Author: Glenn Jacobs
- Created: 2026-06-30
- TODO item: Rename `product_variants.purchasable` to `selling_policy` and reconcile its values

## Problem

`product_variants.purchasable` is a string column holding a **selling-policy mode** — `always`, `in_stock`, or `in_stock_or_on_backorder` — that decides whether a variant can be sold relative to its stock. The name `purchasable` collides with two unrelated concepts on the same line of code:

- **The `Purchasable` morph** — `$line->purchasable` on a cart/order line is the *thing being sold* (a `ProductVariant`, a shipping option), accessed via the `purchasable()` morph relation. `$variant->purchasable` (a selling-policy string) and `$line->purchasable` (a morph target) read identically but mean opposite things.
- **The `customer_group_product.purchasable` boolean** — whether a product is purchasable/visible to a customer group. A third, unrelated meaning of the same word.

Three problems compound the naming collision:

1. **No type.** The mode is a bare string with no enum, no cast, and no constants. Comparisons are stringly-typed: `if ($this->purchasable == 'always')`, `$get('purchasable') !== 'in_stock_or_on_backorder'`. A typo is a silent logic bug.
2. **Inconsistent stored values.** Production code and the Filament form use `in_stock_or_on_backorder`, but a test (`CartLineStockTest`) uses `in_stock_or_backorder` (missing `_on`). The two are meant to be the same mode; nothing reconciles them.
3. **The `always` mode inflates inventory.** `getTotalInventory()`:

   ```php
   public function getTotalInventory(): int
   {
       if ($this->purchasable == 'in_stock') {
           return $this->stock_available;
       }
       return $this->stock_available + $this->backorder;   // 'always' falls here too
   }
   ```

   For `always` (sell regardless of stock), this returns `stock_available + backorder` — it silently treats "always purchasable" as "in stock or on backorder", letting the `backorder` allowance inflate the reported inventory of an item whose policy is to ignore stock entirely.

The richer selling-policy modelling (deny-oversell, sell-against-incoming, a Shopify-style continue-selling boolean) is a **separate post-alpha item** (see `TODO.md` Ideas). **This spec is only the rename plus the value/logic reconciliation** — a schema-reshaping change on a populated column, pulled pre-alpha because renaming it later forces an adopter data migration and breaks adopter queries.

## Proposal

Rename the column to `selling_policy`, back it with a typed enum, reconcile the stored values to one canonical set, and fix the `always` inventory bug.

### The `SellingPolicy` enum

A backed enum in `Enums\SellingPolicy` (TitleCase keys per house style):

```php
enum SellingPolicy: string
{
    case Always = 'always';
    case InStock = 'in_stock';
    case InStockOrOnBackorder = 'in_stock_or_on_backorder';
}
```

`ProductVariant` casts `selling_policy` to it (`'selling_policy' => SellingPolicy::class`), default `SellingPolicy::Always` (matching the current column default). Stringly-typed comparisons become enum comparisons.

### Column rename

The v2 baseline is still open, so the column is renamed **in place** on `create_product_variants_table` (not an alter): `purchasable` → `selling_policy`, keeping the string type, the `always` default, and the index.

### Value reconciliation

The canonical set is the three enum values above. `in_stock_or_backorder` (the `_on`-less variant) is a **test-only typo** — production never stored it — so reconciliation is: standardise the test fixture on `in_stock_or_on_backorder`, and the v1 -> v2 upgrade maps any stored `in_stock_or_backorder` to `in_stock_or_on_backorder` defensively (in case a v1 store hand-set it).

### Fixing the `always` inventory bug

With the enum, the inventory and fulfilment logic reads intent directly and stops adding backorder for `Always`:

```php
public function canBeFulfilledAtQuantity(int $quantity): bool
{
    return $this->selling_policy === SellingPolicy::Always
        || $quantity <= $this->getTotalInventory();
}

public function getTotalInventory(): int
{
    return match ($this->selling_policy) {
        SellingPolicy::Always              => $this->stock_available,            // backorder no longer added
        SellingPolicy::InStock             => $this->stock_available,
        SellingPolicy::InStockOrOnBackorder => $this->stock_available + $this->backorder,
    };
}
```

`Always` short-circuits `canBeFulfilledAtQuantity()` to true (any quantity is fulfillable), so its `getTotalInventory()` figure is never a sell/no-sell gate — it returns the honest physical-available number rather than an inflated `available + backorder`. (See Open questions on whether `Always` should instead report an explicit "unlimited" sentinel.)

### The type-aware Rector rule — the crux

A blanket `->purchasable` rename would corrupt the two unrelated meanings. The rename must touch **only** `ProductVariant`'s selling-policy attribute, never:

- `$line->purchasable` — the `CartLine` / `OrderLine` morph relation (50+ call sites across pipelines, discount types, listeners, validators).
- `customer_group_product.purchasable` — the pivot boolean (`Product` pivot sync, customer-group tests).

`RenamePropertyRector` is **class-scoped** — it rewrites `->purchasable` only on expressions typed as the configured class. So the rule is added to `LunarSetList::V1_TO_V2_PROPERTY_RENAMES`:

```php
['Lunar\\Core\\Models\\ProductVariant', 'purchasable', 'selling_policy'],
['Lunar\\Models\\ProductVariant',       'purchasable', 'selling_policy'],   // v1 namespace
```

Class scoping is what keeps `$line->purchasable` (typed `CartLine`/`OrderLine`) and the pivot boolean untouched. The upgrade notes flag the residual risk: a fully **untyped** `$model->purchasable` where the variable's class can't be inferred won't be rewritten — those are called out for manual review (they are rare; the morph and pivot accesses are the common untyped ones, and those must *not* be renamed anyway).

### Filament and labels

- The form field key `purchasable` -> `selling_policy`; the options array key likewise; the live-dependency check (`$get('purchasable') !== 'in_stock_or_on_backorder'`) updates to the new key.
- Labels reconcile to **"Selling Policy"** everywhere — the `filament` bridge already labels it that; the `admin` package currently says "Purchasability" and is brought into line.
- Translation keys `productvariant.form.purchasable.*` (and the admin package's `productvariant.purchasable.*`) rename to `selling_policy.*`, English-first then mirrored across the other 15 locales, keeping the option labels (Always / In Stock / In Stock or On Backorder).

## Alternatives considered

- **Rename to `selling_policy` but keep it a plain string.** Satisfies the disambiguation but leaves the stringly-typed comparisons and the silent-typo risk. Rejected — the enum is cheap, fixes the `in_stock_or_backorder` class of bug structurally, and is the obvious home for the post-alpha richer modelling.
- **Blanket textual rename of `->purchasable`.** Rejected outright — it would rewrite the line morph and the pivot boolean, corrupting both. The class-scoped Rector rule is mandatory.
- **Fold the richer selling-policy modelling (deny-oversell, sell-against-incoming) into this spec.** Rejected — that is a deliberate post-alpha follow-on (`TODO.md` Ideas) that builds on the renamed column; bundling it would balloon a contained pre-alpha rename into a design-heavy change. This spec stops at rename + reconciliation so the column name stabilises now.
- **Make `Always` report `PHP_INT_MAX` from `getTotalInventory()`.** Models "unlimited" explicitly, but the sentinel leaks anywhere the figure is displayed/serialised — concretely the admin order-items table renders `$record->purchasable?->getTotalInventory()` as a column (`OrderItemsTable.php:83`), which would show `9223372036854775807`. Rejected in favour of the honest physical figure, with `canBeFulfilledAtQuantity()` carrying the unlimited semantics via its short-circuit.

## Migration impact

- **Database** (baseline editable, v2 pre-release): `product_variants.purchasable` renamed to `selling_policy` in `create_product_variants_table` (string, default `always`, indexed — unchanged shape).
- **Breaking changes to the public contract surface:**
  - `ProductVariant->purchasable` (selling-policy attribute) -> `->selling_policy`, now typed `SellingPolicy`. Reads/writes of the **string** attribute break; the morph relation `$line->purchasable` is unaffected.
  - New `Enums\SellingPolicy`. `getTotalInventory()` for `Always` returns a different (lower, correct) number — a behaviour change for any store relying on the old inflated figure; called out in upgrade notes.
  - **Rector rule** in `lunarphp/upgrade` (class-scoped `RenamePropertyRector` entry) migrates typed call sites; untyped accesses flagged for manual review.
- **Upgrade path for v1.x consumers:** the upgrade package renames the column (if not already), maps any `in_stock_or_backorder` -> `in_stock_or_on_backorder`, leaves `always` / `in_stock` / `in_stock_or_on_backorder` as-is, and maps **any other unrecognised value** to a safe default (`always`) so the backed-enum cast cannot throw on a hand-set rogue value after upgrade. One-way (the upgrade data migrations are not reversible; restore from backup to undo). Plus the Rector rule for code. Note: the [[0038-inventory-fundamentals]] backfill migration explicitly left `purchasable` untouched — this spec is the one that renames it, sequenced after that backfill in the upgrade set.
- **Translation / locale impact:** `purchasable.*` selling-policy keys -> `selling_policy.*` across both the `filament` and `admin` packages, English-first then the other 15 locales; option labels preserved.
- **Filament / admin impact:** the variant inventory form field key + label reconcile to `selling_policy` / "Selling Policy"; the live backorder-field dependency updates to the new key; the admin package label changes from "Purchasability" to "Selling Policy".

## Resolved decisions

- **`Always` returns `stock_available` from `getTotalInventory()`.** The honest physical figure, not a `PHP_INT_MAX` sentinel — `getTotalInventory()` is rendered directly in the admin order-items column, so a sentinel would surface as a garbage number. `canBeFulfilledAtQuantity()` carries the "unlimited" semantics by short-circuiting to true for `Always`. This also fixes the bug where `Always` added the `backorder` allowance.
- **`SellingPolicy` is a backed enum, cast on the model.** The Filament Select keeps its options keyed by the enum's backing strings (`'always'`, `'in_stock'`, `'in_stock_or_on_backorder'`), so it binds cleanly through the cast; factories and tests set the enum (or its backing value).
- **The stored value strings are kept as-is** (`in_stock_or_on_backorder` is not shortened). The enum supplies the ergonomic name (`SellingPolicy::InStockOrOnBackorder`); changing the stored string would only add upgrade-mapping surface for no gain.
- **The upgrade reconciliation is total** — every stored value maps to a valid enum case (the typo is corrected, recognised values pass through, anything unexpected falls back to `always`), so the cast cannot throw post-upgrade.

## References

- `Lunar\Core\Models\ProductVariant` — `getTotalInventory()` / `canBeFulfilledAtQuantity()` and the `purchasable` attribute.
- `customer_group_product.purchasable` (pivot boolean) and `CartLine`/`OrderLine::purchasable()` (morph) — the two collisions the rename disambiguates and the Rector rule must **not** touch.
- `packages/upgrade/src/Rector/LunarSetList.php` — `V1_TO_V2_PROPERTY_RENAMES` / `RenamePropertyRector`, the class-scoped rename pattern this rule follows (modelled on the `compare_price` -> `list_price` entry).
- [[0038-inventory-fundamentals]] — the stock rollup `getTotalInventory()` reads; left `purchasable` untouched, deferring the rename here.
- Upgrade data migrations are one-way (not reversible; restore from backup to undo).

## Implementation plan

- [ ] Slice 1 — enum + rename + logic. `Enums\SellingPolicy`; rename the column in `create_product_variants_table`; cast on `ProductVariant`; rewrite `getTotalInventory()` / `canBeFulfilledAtQuantity()` to the `match` (fixing the `always`-adds-backorder bug); update factories/tests, standardising the `in_stock_or_backorder` fixture typo.
- [ ] Slice 2 — Filament + locales. Form field key/label -> `selling_policy` / "Selling Policy" in both packages; live backorder dependency updated; translation keys renamed across 16 locales.
- [ ] Slice 3 — upgrade package. Column rename + total value-reconciliation data migration (typo corrected, recognised values passed through, unknowns -> `always`; one-way); class-scoped `RenamePropertyRector` entry for `ProductVariant`; upgrade notes flagging the `always` inventory behaviour change and untyped-access manual-review caveat.
