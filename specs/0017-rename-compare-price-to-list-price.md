# 0017 — Rename `compare_price` to `list_price`

- Status: implemented
- Author: Glenn Jacobs
- Created: 2026-05-26
- TODO item: Change `compare_price` to `list_price`

## Problem

The `prices` table stores a secondary "before discount / RRP" amount in a column named `compare_price`. The name describes a UI behaviour (a price shown for comparison) rather than the thing it holds. The wider e-commerce vocabulary — and the schemas Lunar integrates with (Shopify, schema.org `listPrice`, most ERPs) — call this the *list price*. The mismatch shows up everywhere the value surfaces: the column, the `Price` model attribute and its `comparePriceIncTax()` accessor, the admin pricing forms, and 16 locales of relation-manager translations.

## Proposal

Rename the concept to `list_price` end to end. No behavioural change — the value, nullability, and money semantics stay identical.

### Schema

`prices.compare_price` (`unsignedBigInteger`, nullable) becomes `prices.list_price`. v2 is unreleased, so edit the baseline migration directly rather than create-then-rename — the baseline stays clean.

`packages/core/database/migrations/2026_01_01_000029_create_prices_table.php`:

```php
$table->unsignedBigInteger('list_price')->nullable();
```

### Core model

`packages/core/src/Models/Price.php`:

- PHPDoc `@property ?int $compare_price` → `@property ?int $list_price`.
- `$casts` key `compare_price` → `list_price`.
- Rename the accessor `comparePriceIncTax(?TaxZoneContract $taxZone = null): PriceValue` → `listPriceIncTax(...)` and read `$this->list_price`.

`comparePriceIncTax()` is v2-only (it returns a `PriceValue`, spec 0015), so the rename is internal to v2 development and needs no Rector rule.

### Core service layer

- `Actions/Currencies/CreateCurrencyPrices.php` — the `DB::raw('ROUND(compare_price * …) as compare_price')` select and the column list.
- `Jobs/Currencies/SyncPriceCurrencies.php` — both `$this->price->compare_price` reads / writes.
- `database/factories/PriceFactory.php` — the `compare_price` state key.

### Admin / Filament

- `admin/src/Support/Concerns/Products/ManagesProductPricing.php` — form field, `statePath`, the `original_compare_price` baseline key used in the dirty-check, and the currency-exchange mapping.
- `admin/src/Support/RelationManagers/PriceRelationManager.php` — the `TextInput::make('compare_price')` field and the `['price', 'compare_price']` loop.
- `filament/src/RelationManagers/Product/CustomerGroupPricingRelationManager.php` — same field rename.

### Translations (16 locales × 2 packages)

Rename the `compare_price` translation key to `list_price` in `relationmanagers.php` under both `packages/admin/resources/lang/` and `packages/filament/resources/lang/`. Two occurrences per file (the top-level price form and the nested `basePrices.form`). Update the English copy first — `'label' => 'List Price'`, helper text "The list price or RRP, shown for comparison with the purchase price." — then mirror the key across the other 15 locales (English value acceptable as placeholder).

### Upgrade package (v1 → v2)

v1 shipped `compare_price` as a real persisted column and public model attribute, so consumers need both a schema migration and a code rewrite.

- **Data migration** — add `packages/upgrade/database/migrations/<ts>_rename_compare_price_to_list_price.php` that runs `Schema::table($prefix.'prices', fn (Blueprint $t) => $t->renameColumn('compare_price', 'list_price'))`, guarded by `Schema::hasColumn(...)`, with the inverse in `down()`. Picked up automatically by `DataMigrationStep`.
- **Property rename Rector** — rewrite `$price->compare_price` → `$price->list_price` in user code. Add a `V1_TO_V2_PROPERTY_RENAMES` map to `LunarSetList` keyed by `Lunar\Core\Models\Price` (and the `Lunar\Models\Price` alias) and wire Rector's built-in `RenamePropertyRector` in `config/rector.php`.
- **Money-attribute maps** — update the `compare_price` entries in `V1_TO_V2_MONEY_ATTRIBUTES` (`LunarSetList.php` ~lines 562/583) to `list_price` so the existing money-attribute rewrites (`DropPriceValueAccessRector`, `RewritePriceDecimalCallRector`, …) target the new name.

### Tests

- Update any core pricing tests asserting on `compare_price` (model, `CreateCurrencyPrices`, `SyncPriceCurrencies`) and admin pricing tests.
- Add an upgrade test covering the column rename migration and the property-rename Rector.

## Alternatives considered

- **Do nothing** — keep `compare_price`. Rejected: the name actively misleads and diverges from every integration target; cheaper to fix now while v2 is unreleased than after.
- **New migration instead of editing the baseline** — would leave a create-then-rename pair in the unreleased baseline for no benefit. Rejected per the reasoning above; the "don't edit the baseline" rule protects *shipped* schema.
- **Keep the `comparePriceIncTax()` method name** while renaming the column — rejected; leaving the accessor out of step with the attribute reintroduces exactly the naming drift this spec removes.

## Migration impact

- **Database migrations**: baseline `prices` migration edited (fresh v2 installs); one new upgrade migration renames the column on existing v1 databases.
- **Breaking changes to the public contract surface**: `prices.compare_price` column and `Price::$compare_price` attribute are public. The `comparePriceIncTax()` accessor is v2-only and not yet released. v1 consumers are covered by the upgrade migration + property-rename Rector.
- **Upgrade path for v1.x consumers**: handled in the `upgrade` package (data migration + Rector), consistent with spec 0001.
- **Translation / locale impact**: `list_price` key replaces `compare_price` in `relationmanagers.php` across all 16 locales in both `admin` and `filament`.
- **Filament / admin impact**: pricing relation managers and the product pricing form fields rename to `list_price`; no layout or behaviour change.

## Open questions

None.

## References

- TODO.md — "Change `compare_price` to `list_price`".
- `[[0012-price-data-type-refactor]]` — price cast / money-attribute machinery this rename rides on.
- `[[0015-pricevalue-arithmetic]]` — origin of the `…IncTax()` `PriceValue` accessors.
- `[[0001-upgrade-package]]` — upgrade-step + Rector conventions.
