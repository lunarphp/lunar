# 0008 — Reusable Filament entity-selector components

- Status: implemented
- Author: Glenn Jacobs
- Created: 2026-05-23
- TODO item: "Reusable Filament entity-selector components"

## Problem

Picking a related entity in a Filament form, table action, or relation manager is one of the most common things Lunar's admin does. Today every call site builds its own `Select`, with no shared component to lean on. The result is 17+ near-duplicate implementations with material behavioural differences between them.

Concrete examples from `packages/admin/` and `packages/filament/`:

- **Product** is selectable in 6 places. Three different search backends are in play:
  - `get_search_builder(Product::class, $search)` in `ManageProductAssociations`, `ManageCollectionProducts`, every Discount product RelationManager.
  - Scout's `Product::search($search)` in `ManageBrandProducts`.
  - Plain `Product::query()->where(...)` in a couple of ad-hoc cases.
  Each call site also picks its own label helper — `translateAttribute('name')` in some, the `attr('name')` Twig-style helper in others. Only `ManageCollectionProducts` deduplicates already-attached products; the other five silently let the user re-attach the same record.
- **ProductVariant** is selectable in 3 places with two fundamentally different strategies — direct variant search in two RelationManagers, search-products-then-filter-variants in a third. The result label format ("Product name — SKU") matches by coincidence, not by contract.
- **Collection** is selectable in 5 places. Some show a breadcrumb path label ("Parent > Child > Leaf"), others show only the leaf name. Some use a bare `Select`, others wrap with `AttachAction::make()->getRecordSelect()`. `EditCollection` adds a unique "reject descendants" filter when picking a re-parent target on delete; that logic is not reusable.
- **Brand**, **CustomerGroup**, **Country**, **Customer** each have 2–3 implementations with the same pattern: inconsistent backend, inconsistent labelling, ad-hoc scope filters.

The only shared selector-shaped components today are `AttributeSelector` (a `CheckboxList` for product-type attributes), `PermissionSelector` (a custom component for staff permissions), and `Tags` (the product tag input). None are general-purpose entity pickers.

This duplication has three knock-on costs:

1. **Inconsistent UX.** A user picking a product on the Discount conditions screen sees a different search experience than picking the same product on the Brand or Collection attach screen. Already-attached deduplication, image thumbnails, and SKU-in-search are present in some places and missing in others.
2. **Bug surface.** When the Scout integration breaks, or a new searchable field gets added, or a model gets a `status` scope, every selector has to be patched independently. The Scout-vs-`get_search_builder` split has already drifted — the same form can call both depending on which path the user takes.
3. **Bridge package can't deliver on its promise.** Spec [[0006-filament-bridge-package]] moves the per-resource schemas/tables into `lunarphp/filament` so a downstream developer can compose them into their own panel. But a developer building a custom Filament page outside Lunar's resources still has to handwrite a product picker from scratch — there's no `ProductSelect::make('product_id')` to drop in. The bridge package needs the lower-level building blocks too.

## Proposal

Ship a set of reusable selector components under `packages/filament/src/Forms/Components/`, each a thin `Filament\Forms\Components\Select` (or `MorphToSelect`) subclass with Lunar-specific defaults wired in. Migrate every existing call site to use them. Document them as the recommended way to pick a Lunar entity in any Filament context.

### Package layout

All new components land in the bridge package alongside the existing handful:

```
packages/filament/src/Forms/Components/
    BrandSelect.php
    ChannelSelect.php
    CollectionSelect.php
    CountrySelect.php
    CurrencySelect.php
    CustomerGroupSelect.php
    CustomerSelect.php
    DiscountTargetSelect.php
    LanguageSelect.php
    ProductSelect.php
    ProductTypeSelect.php
    ProductVariantSelect.php
    StateSelect.php
    TagSelect.php
    TaxClassSelect.php
    TaxZoneSelect.php
    Concerns/
        SearchesLunarRecords.php
        ExcludesAttachedRecords.php
    Support/
        RecordSearch.php
```

Namespace: `Lunar\Filament\Forms\Components\…` (the existing convention from spec 0006).

Each component:

- Extends `Filament\Forms\Components\Select` (or `MorphToSelect` for the discount target picker).
- Provides Lunar-flavoured defaults: searchable, the right search backend, a sensible label/description, image/thumbnail rendering where the model has media, the right `relationship()` wiring where applicable.
- Exposes a fluent API on top of Filament's so call sites can extend behaviour without dropping back to base `Select`. Examples: `->scopeActive()`, `->withinChannel($channel)`, `->excludeAttached()`, `->excludeDescendantsOf($collection)`, `->showSku()`, `->preloadDefaults()`.

### Cross-cutting decisions

The selectors are useful only if they agree on the cross-cutting bits. The current duplication exists because each call site decided these independently — the spec locks them down.

#### Search backend

One backend, used by every selector: a new `Lunar\Filament\Forms\Components\Support\RecordSearch` service. It encapsulates the existing `get_search_builder()` logic — prefer Scout when both enabled and the model uses `Searchable`, otherwise fall back to translated-attribute DB search across the model's configured searchable fields. The current free function in `packages/admin/src/helpers.php` is deprecated and kept as a thin proxy for the v2 cycle, then removed in v3.

`RecordSearch` is the only thing that calls `Product::search()` or builds the translated-name `where` clause. Selectors call `RecordSearch::for($model, $search)`. New search behaviour (additional searchable columns, custom scope, channel filter) is configured on the selector, not the backend.

#### Label rendering

One label helper used by every selector: `translateAttribute('name')` for translated names; selector subclasses override `getOptionLabel(Model $record)` for entity-specific shapes ("Product name — SKU" for variants, breadcrumb path for collections, emoji + native name for countries). The Twig-style `attr()` helper is no longer used inside selectors — its remaining call sites in the discount RelationManagers get rewritten.

#### Already-attached deduplication

A `ExcludesAttachedRecords` concern. When the selector is mounted inside a relation manager's `AttachAction`, calling `->excludeAttached()` automatically filters the search results by the IDs already on the relation. Lifts the explicit `.reject(...)` logic currently in `ManageCollectionProducts` into one place; the other five product selectors gain the behaviour for free.

#### Single vs multiple

Each selector accepts `->multiple()` from Filament's base `Select`. Components do not pick a default cardinality — call sites declare it explicitly so the choice is visible at the use site.

#### Preloading

Components that wrap small lookup tables (`CurrencySelect`, `ChannelSelect`, `LanguageSelect`, `TaxClassSelect`, `ProductTypeSelect`) call `->preload()` by default. Components over large tables (`ProductSelect`, `CustomerSelect`, `CollectionSelect`, `ProductVariantSelect`) do not preload — they are searchable-only by default with an opt-in `->preloadDefaults()` for cases where the call site wants the first N records eagerly.

### Component summary

| Component | Backs | Default columns shown in search | Notable extras |
|---|---|---|---|
| `ProductSelect` | `Product` | translated name, status pill | `->showSku()`, `->showThumbnail()`, `->scopeStatus(...)`, `->withinChannel(...)`, `->excludeAttached()` |
| `ProductVariantSelect` | `ProductVariant` | "Product name — SKU" | `->forProduct(Product)`, `->showStock()`, `->excludeAttached()` |
| `CollectionSelect` | `Collection` | breadcrumb path | `->excludeDescendantsOf(Collection)` (re-parent safety), `->withinGroup(CollectionGroup)`, `->excludeAttached()` |
| `BrandSelect` | `Brand` | translated name | `->excludeAttached()`, inline `->createOptionForm(...)` preserved |
| `CustomerSelect` | `Customer` | "First Last — Company" or email | multi-field search across `first_name`, `last_name`, `company_name`, `email`; `->excludeAttached()` |
| `CustomerGroupSelect` | `CustomerGroup` | name | preloaded; `->withPivotFields(...)` for AttachAction-style usage with pivot data |
| `CurrencySelect` | `Currency` | name + code | preloaded; defaults to `Currency::getDefault()`; `->onlyEnabled()` |
| `ChannelSelect` | `Channel` | name | preloaded; defaults to current channel; `->onlyEnabled()` |
| `LanguageSelect` | `Language` | name + code | preloaded; defaults to default language |
| `CountrySelect` | `Country` | emoji + name | `->iso3()` mode, `->multiple()` supported, `->onlyEnabled()` |
| `StateSelect` | `State` | name | `->dependsOn('country_id')` reuses the current datalist pattern as a proper component |
| `TaxClassSelect` | `TaxClass` | name | preloaded; `->default(TaxClass::getDefault())` |
| `TaxZoneSelect` | `TaxZone` | name + type | preloaded |
| `ProductTypeSelect` | `ProductType` | name | preloaded; `->live()` |
| `TagSelect` | `Tag` | tag value | multiple by default; replaces the bespoke `Tags` component for non-product use cases (`Tags` stays for product tag suggestions) |
| `DiscountTargetSelect` | morphs over `Product`, `ProductVariant`, `Collection`, `Brand` | per-type | `MorphToSelect` subclass; one component that the three Discount RelationManagers (condition / limitation / reward) configure per context |

### Call-site migration

Existing call sites are rewritten in lockstep with each component landing. Concretely, after this spec:

- `ManageProductAssociations`, `ManageBrandProducts`, `ManageCollectionProducts`, `ProductConditionRelationManager`, `ProductLimitationRelationManager`, `ProductRewardRelationManager` — all use `ProductSelect::make('…')->excludeAttached()`.
- `ProductConditionRelationManager`, `ProductVariantLimitationRelationManager`, `ProductRewardRelationManager` — all use `ProductVariantSelect::make('…')`.
- `ManageProductCollections`, `ManageBrandCollections`, `CollectionLimitationRelationManager`, `CollectionConditionRelationManager` — all use `CollectionSelect::make('…')`. `EditCollection`'s re-parent picker uses `CollectionSelect::make('target_collection')->excludeDescendantsOf($record)->excludeSelf($record)`.
- `BrandLimitationRelationManager`, `ProductForm` — `BrandSelect::make('…')`.
- `CustomerLimitationRelationManager` — `CustomerSelect::make('…')`.
- `PriceRelationManager`, `CustomerGroupPricingRelationManager` — `CurrencySelect::make('currency_id')` and `CustomerGroupSelect::make('customer_group_id')`.
- `DisplaysOrderAddresses`, `Customer/AddressRelationManager`, `TaxZoneForm` — `CountrySelect::make('…')` / `StateSelect::make('…')->dependsOn('country_id')`.
- All three Discount target managers — `DiscountTargetSelect::make('…')->targets([Product, ProductVariant, Collection, Brand])`.

Each migration is a small, mechanical PR. The selector ships with its call-site rewrites — the spec doesn't accept landing a selector without retiring at least one duplicate.

### Public API shape

Every selector follows the same fluent surface so call sites are predictable:

```php
ProductSelect::make('product_id')
    ->label(__('lunar-filament::forms.product.label'))
    ->required()
    ->excludeAttached()       // dedupes against the current relation when used in a RelationManager AttachAction
    ->withinChannel($channel) // optional scope
    ->showSku()               // optional column in the search dropdown
    ->showThumbnail();        // optional thumbnail
```

Anything not listed in the component's fluent surface is inherited from Filament's `Select` — `->disabled()`, `->visible()`, `->live()`, `->afterStateUpdated()`, `->relationship()` etc. all work as normal. The selectors do not hide Filament's API; they layer Lunar-specific defaults on top.

### Translation strategy

A new `forms/selectors.php` lang file in `packages/filament/resources/lang/{locale}/` — keys for each selector's default label, placeholder, "no results" message, and any per-entity search hint. English first, then mirrored across the other 15 locales (English value acceptable as a placeholder where no translation exists yet). Selector components reference these keys as their default `->label()` and `->placeholder()`, so call sites that don't specify a label get a translated one for free.

### Publishable example

The bridge's existing `vendor:publish --tag=lunar-filament.schemas` publishable group covers schema/table/infolist classes. The selectors are *not* part of that group — they're runtime building blocks, not consumer-owned templates. A separate doc page demonstrates how to compose them into a custom Filament page (e.g. a bulk import form that picks a brand and a collection in a custom action).

### Test surface

Each component gets a Pest test in `packages/lunar/tests/Filament/Forms/Components/` covering:

- Mounting the component in a standalone Filament panel boot (no `LunarPanelProvider` in scope — proves bridge-package isolation).
- Default search returns expected records.
- Fluent options compose (`->excludeAttached()` actually excludes; `->scopeStatus(...)` actually filters; etc.).
- The label-helper renders the right shape for the entity.

The discount `DiscountTargetSelect` gets an integration test against the three Discount RelationManagers to verify the morph configuration round-trips.

### PR slicing

One PR per logical group. Each PR introduces the component(s), retires the matching call sites, lands the translation keys, and adds tests.

1. **Foundations** — `Support/RecordSearch`, `Concerns/SearchesLunarRecords`, `Concerns/ExcludesAttachedRecords`, base translation file. No selectors yet.
2. **Catalog selectors** — `ProductSelect`, `ProductVariantSelect`, `CollectionSelect`, `BrandSelect`, `ProductTypeSelect`, `TagSelect`. Migrates the 14 catalog-family call sites.
3. **Sales selectors** — `CustomerSelect`, `CustomerGroupSelect`, `DiscountTargetSelect`. Migrates the customer / discount call sites.
4. **Settings selectors** — `CurrencySelect`, `ChannelSelect`, `LanguageSelect`, `TaxClassSelect`, `TaxZoneSelect`, `CountrySelect`, `StateSelect`. Migrates the settings / address call sites.
5. **Cleanup** — deprecate the `get_search_builder()` helper (proxy through to `RecordSearch::for(...)`), drop now-unused inline search closures, run `vendor/bin/pint --dirty`, regenerate translations, document the public API on the docs site.

## Alternatives considered

- **A single `LunarSelect` component with an entity argument** (`LunarSelect::make('product_id')->for(Product::class)`). Rejected — entity-specific fluent options (`->showSku()` only makes sense for products/variants; `->excludeDescendantsOf()` only makes sense for collections; `->dependsOn('country_id')` only makes sense for states) end up living on a single class and either become noise or hide behind dynamic method calls. One subclass per entity gives IDE autocomplete that matches what's actually valid for that entity.
- **A single trait that resources mix into ad-hoc `Select::make()` calls.** Rejected — a trait can share search logic but not a public surface. Call sites still have to wire `->searchable()->getSearchResultsUsing(...)` by hand, and the inconsistencies the spec is trying to fix come back the first time someone forgets a step.
- **Leave selectors as-is and document the canonical pattern in CLAUDE.md / contribution docs.** Rejected — docs don't fix existing duplication, don't get applied to downstream Filament pages outside this repo, and don't give IDE autocomplete. The bridge package exists specifically so downstream developers can drop in Lunar's pieces; selectors are the highest-leverage piece they're missing.
- **Use Filament's `ModalTableSelect` plugin for every selector instead of inline `Select` dropdowns.** Rejected for v2 — the modal-table approach is a strictly heavier UX than the current dropdown. Worth revisiting once the components exist, since flipping the implementation behind the selector's facade would be a one-shot upgrade for every call site.
- **Ship the selectors in `lunarphp/admin` instead of `lunarphp/filament`.** Rejected — the whole point of the bridge package (spec 0006) is that downstream Filament panels can compose Lunar's pieces. Selectors are the building blocks; they belong in the bridge.

## Migration impact

- **Database**: none.
- **Public contract surface**: net additive. The 16 new component classes are new public API on `lunarphp/filament`. The `get_search_builder()` helper is deprecated; existing callers keep working. No existing class signature changes — call sites move to the new components voluntarily.
- **Upgrade path for v1.x consumers**: there are no v1 selectors to migrate, since this is new surface. The deprecation of `get_search_builder()` lands a Rector rule in `lunarphp/upgrade` that rewrites direct callers to `RecordSearch::for(...)`; downstream consumers who built their own helpers around it are nudged forward.
- **Translation impact**: one new lang file (`forms/selectors.php`) in `packages/filament/resources/lang/`, 16 locales.
- **Filament / admin impact**: the in-repo migration of 17+ call sites. None of them change behaviour for end users beyond consistency fixes (deduplication everywhere, breadcrumbs everywhere, SKU search on variants everywhere).

## Open questions

- **Bridging table columns and infolist entries too.** Several call sites also render the selected entity in a table column or infolist entry afterwards (`TextColumn::make('product.name')->formatStateUsing(...)`). A symmetric `ProductColumn` / `ProductEntry` family is a natural follow-up — out of scope here, worth a sibling spec.
- **Where to draw the line on entity-specific fluent options.** `ProductSelect::scopeStatus()` is obvious; `ProductSelect::withinChannel()` is obvious; `ProductSelect::excludeArchived()` could be either a built-in or an application of `->scopeStatus(...)`. Recommend: built-in only for behaviour Lunar's own admin needs in v2. Anything else lives as documented `->modifyOptionsQuery(...)` recipes.
- **`ProductVariantSelect` cross-product search**. Two of the three current implementations search products and filter variants; one searches variants directly. Which is the default? Recommend: direct variant search by default (SKU + product name via the searchable-attributes config), with an explicit `->searchViaProduct()` opt-in for the case where the user knows the product but not the variant.
- **Replacing `Tags` for non-product use cases.** The existing `Tags` component is product-specific. Adding a general `TagSelect` raises the question of whether `Tags` should become a thin wrapper over `TagSelect::make()->withSuggestionsFor(Product)`. Recommend yes, but track as a follow-up since it touches the product form.

## References

- [[0006-filament-bridge-package]] (implemented) — established the bridge package this spec extends.
- [[0005-filament-v5-schemas-refactor]] (completed) — established the split-class shape that the migrated call sites already follow.
- `packages/lunar/packages/admin/src/helpers.php` — current `get_search_builder()` helper; becomes a deprecated proxy.
- `packages/lunar/packages/filament/src/Forms/Components/` — destination directory for the new components; sits alongside existing `AttributeSelector`, `PermissionSelector`, `Tags`, `MediaSelect`.
- `packages/lunar/packages/admin/src/Filament/Resources/ProductResource/Pages/ManageProductAssociations.php` — representative existing call site (product picker).
- `packages/lunar/packages/admin/src/Filament/Resources/CollectionResource/Pages/EditCollection.php` — the unique re-parent-target case `CollectionSelect::excludeDescendantsOf()` is designed for.
- `packages/lunar/packages/filament/src/RelationManagers/Discount/` — the three discount RelationManagers driving `DiscountTargetSelect`.
- `packages/lunar/packages/filament/src/RelationManagers/Customer/AddressRelationManager.php` and `packages/lunar/packages/admin/src/Filament/Resources/OrderResource/Concerns/DisplaysOrderAddresses.php` — current country/state datalist pattern that `CountrySelect` / `StateSelect` formalise.
