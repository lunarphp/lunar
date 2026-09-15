# 0085 — Product bundles (`lunarphp/bundles`)

- Status: accepted
- Author: Glenn Jacobs
- Created: 2026-09-15
- TODO item: Product bundles — sell a set of variants as one product with derived stock and component fulfilment
- Target branch: `2.x` (monorepo) and `next` (docs)

## Problem

Lunar has no way to sell a kit. A merchant who wants "Camera starter kit: body + bag + SD card" at one price has two poor options today:

- Create a normal product for the kit. Stock is then tracked on the kit SKU independently of its parts, so the kit sells out (or oversells) without regard to the components, and the warehouse pick list shows "starter kit x1" with no parts to pick.
- Use a `BuyXGetY` discount. The customer has to add every part separately, there is no single "kit" product to list, merchandise or search, and the saving is a promotion, not a price.

The core was designed with this in mind. `Purchasable` and `TracksStock` are separate contracts, and `TracksStock`'s docblock names "a bundle" as a motivating custom purchasable. Spec 0030 lists bundles as a downstream `Purchasable`. Nothing has been built on that foundation.

Concretely, the seams a bundle needs today are these:

- Stock: `ProductVariant::getTotalInventory()` and `canBeFulfilledAtQuantity()` read the variant's own rollup columns. There is no way for an add-on to answer them from other variants' stock.
- Order lines: `CreateOrderLines`, `CleanUpOrderLines` and `MapDiscountBreakdown` identify a line by purchasable plus a `meta` diff. There is no notion of a line that belongs to another line, so nothing can add component lines to an order without those stages deleting or mis-mapping them.
- Fulfilment and stock commitment both walk `order_lines` by purchasable (`SyncsTrackedStock`, `SyncStockCommitment`, `FulfilmentLine`). A bundle sold as one line never commits or picks its parts.
- Pricing: `PricingManager::get()` throws `MissingCurrencyPriceException` before the `lunar.pricing.pipelines` stages run, so a purchasable whose price is derived from other purchasables has no runtime hook.

## Proposal

Three deliverables, in order:

1. **Two small, generic seams in `lunarphp/core`**: component order lines (`order_lines.parent_line_id`) and a resolver seam behind `ProductVariant`'s inventory answers. Both usable by any add-on that sells composites (gift sets, subscriptions with a starter kit, made-to-order assemblies).
2. **A new package `lunarphp/bundles`** (`packages/bundles`, namespace `Lunar\Bundles`) that makes any product variant a bundle of other variants, with fixed or component-derived pricing, stock derived from components, and component lines on the order so fulfilment and stock commitment work unchanged. It ships an Inertia panel extension.
3. **A docs PR** to `lunarphp/docs`.

### Design position: a bundle is a product

A bundle is a normal `Product` with normal `ProductVariant`s. The package attaches a component list to a **variant** (the sellable unit) through its own tables. Nothing about the product, its media, attributes, URLs, collections, channels, customer groups, search indexing, tax class, discount targeting, `public_id`, cache tags or the panel product editor changes.

That decision is what keeps the package small. The alternative, a standalone `Bundle` purchasable, is rejected in "Alternatives considered": the core has more than a dozen sites that assume a cart or order line's purchasable is a `ProductVariant`, the storefront lists products, and the panel's product editing components are not on the add-on surface.

Two consequences follow, and they are the only behaviour the package has to add:

- **The bundle variant's own stock is ignored.** Availability is derived from the components through the new inventory seam.
- **At order creation the bundle line gains component lines.** Fulfilment, stock commitment, returns and location allocation then see the parts, because they already work from order lines.

Terminology used throughout: the **bundle variant** is the `ProductVariant` that carries the components; a **component** is a `(product_variant, quantity)` pair inside it; a **group** is an optional choice set inside a configurable bundle; a **selection** is the customer's resolved list of components for one cart line.

### Part 1: seams in `lunarphp/core`

#### 1.1 Component order lines

`order_lines` gains a nullable self-referencing `parent_line_id` (folded into the baseline migration while v2 is in alpha):

```php
$table->foreignId('parent_line_id')->nullable()->index()
    ->constrained($this->prefix.'order_lines')->cascadeOnDelete();
```

`OrderLine` gains:

```php
public function parent(): BelongsTo;      // ?OrderLine
public function components(): HasMany;    // OrderLine, ordered by id
public function isComponent(): bool;      // parent_line_id !== null
public function scopeTopLevel(Builder $query): void;   // whereNull('parent_line_id')
```

A component line is a real order line: it has a purchasable, `type`, `requires_shipping`, `requires_fulfilment`, `identifier` and `quantity`, so every consumer that already walks order lines to fulfil, allocate, ship, return or commit stock keeps working. What it does not carry is money: `unit_price`, `sub_total`, `discount_total`, `tax_total` and `total` are `0` and `tax_breakdown` is empty. The parent line is the customer's contract and holds the price, the tax and the refundable amount. Component lines carry `meta.allocated_total` (see 2.6) for reporting only.

Core stages that identify order lines by purchasable plus `meta` skip component lines, because a component line is never the image of a cart line:

- `CreateOrderLines` matches against `$order->lines->whereNull('parent_line_id')`.
- `CleanUpOrderLines` iterates `$order->productLines->whereNull('parent_line_id')`. Component lines are removed with their parent through the cascade.
- `MapDiscountBreakdown` maps `$order->lines->whereNull('parent_line_id')`.

`EnsureInitialFulfilment` and the fulfilment methods are untouched: they already work from `fulfillableLines()`, and a parent line is stamped `requires_fulfilment = false` (2.6), so only the components are claimed.

Refunds are parent-only. `RefundLine` continues to point at any order line, but `OrderLine::refundableQuantity()` returns `0` for a component line, and the panel refund composer lists top-level lines only.

Panel (`lunarphp/panel`, first party):

- `OrderShowController` builds `otherLines` and `refundableLines` from top-level lines.
- Fulfilment line rows (`FulfilmentLineRow.vue`) render a "part of {parent description}" hint when the order line is a component, so the pick list reads "Camera body x1 — part of Camera starter kit".
- The non-fulfillable lines card shows the parent line with its components nested beneath it, quantities only.

Storefront guidance (docs): order history renders `$order->lines()->topLevel()->with('components')` and nests components under their parent.

#### 1.2 Inventory resolver seam

`ProductVariant::getTotalInventory()` and `canBeFulfilledAtQuantity()` delegate to an action so an add-on can answer them from somewhere other than the variant's own rollup columns:

```php
namespace Lunar\Core\Contracts\Actions\Products;

interface ResolvesInventory
{
    public function execute(ProductVariant $variant): VariantInventory;
}
```

```php
namespace Lunar\Core\DataObjects;

final readonly class VariantInventory
{
    public function __construct(
        public int $available,     // units that can be sold now
        public bool $unlimited,    // selling continues regardless of $available
    ) {}

    public function allows(int $quantity): bool
    {
        return $this->unlimited || $quantity <= $this->available;
    }
}
```

The default implementation, `Actions/Products/ResolveInventory`, moves the existing `match ($this->selling_policy)` logic out of the model unchanged. `ProductVariant` becomes:

```php
public function getTotalInventory(): int
{
    return app(ResolvesInventory::class)->execute($this)->available;
}

public function canBeFulfilledAtQuantity(int $quantity): bool
{
    return app(ResolvesInventory::class)->execute($this)->allows($quantity);
}
```

Registered in `ActionServiceProvider::$actions`. Spec 0082 (selling-policy rework) changes the formula inside `ResolveInventory`, not the seam, so the two specs compose. `ShippingOption` and custom purchasables are unaffected.

The bundles package decorates the binding (`$this->app->extend(ResolvesInventory::class, ...)`): a bundle variant's inventory is computed from its components; every other variant falls through to the inner action.

### Part 2: `lunarphp/bundles`

#### 2.1 Package skeleton

```
packages/bundles/
  composer.json               name lunarphp/bundles, requires lunarphp/core; lunarphp/panel is suggested
  config/bundles.php          merged as lunar.bundles
  database/migrations/        three tables, all extending Lunar\Core\Database\Migration
  database/factories/         BundleFactory, BundleGroupFactory, BundleComponentFactory
  resources/lang/{16 locales}/bundles.php
  resources/js/               panel add-on (Vue), built like packages/panel-addon-example
  build/                      compiled panel bundle, tracked (CI commits it)
  src/
    BundlesServiceProvider.php
    Contracts/Actions/{DefinesBundle,SyncsBundleComponents,SyncsBundleGroups,RepricesBundle,
                       ResolvesBundleSelection,DeletesBundle}.php
    Actions/{DefineBundle,SyncBundleComponents,SyncBundleGroups,RepriceBundle,
             ResolveBundleSelection,DeleteBundle,ResolveBundleInventory}.php
    Models/{Bundle,BundleGroup,BundleComponent}.php
    Enums/BundlePricing.php
    ValueObjects/{BundleSelection,SelectedComponent}.php
    Pipelines/CartLine/PriceBundleSelection.php    cart_lines pipeline stage
    Validation/CartLine/BundleSelection.php        add_to_cart / update_cart_line validator
    Pipelines/Order/Creation/CreateBundleComponentLines.php
    Listeners/{RepriceOnComponentPriceChange,RepriceOnComponentInvalidation,InvalidateContainingBundles}.php
    Events/BundleInvalidated.php
    Console/RepriceBundlesCommand.php
    Exceptions/{InvalidBundleSelection,BundleNesting}.php
    Panel/{BundlesSectionExtension.php, Http/Controllers/*, Http/Requests/*, Tables/BundleColumn.php,
           Tables/BundlesOnlyFilter.php, Tables/ProductsTableExtension.php}
```

`composer.json` follows `search-relevance`: `type: library`, psr-4 for `Lunar\Bundles\` and `Lunar\Bundles\Database\Factories\`, `extra.lunar.name = "Bundles"`, `extra.laravel.providers`, `suggest: lunarphp/panel`. Monorepo wiring: root `composer.json` (psr-4 x2, autoload-dev, `extra.lunar.name`, providers, `replace`), `phpunit.xml` testsuite `bundles`, `.github/workflows/tests.yml` matrix plus a `panel-js` build step, `split_packages.yml`, `build_panel_assets.yml`, root `package.json` workspace. `monorepo-builder.php` and `phpstan.neon.dist` need nothing.

#### 2.2 Database

**`{prefix}bundles`** — one row per bundle variant.

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| public_id | ulid unique | |
| product_variant_id | FK product_variants, unique, cascadeOnDelete | the bundle variant |
| pricing | string(16) | `fixed` or `components` (`BundlePricing` enum) |
| discount_percentage | decimal(5,2) null | only meaningful with `components`; `null` means no saving |
| timestamps | | |

**`{prefix}bundle_groups`** — a choice set inside a configurable bundle.

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| public_id | ulid unique | referenced from cart line meta |
| bundle_id | FK bundles, cascadeOnDelete | |
| name | json | translated |
| min_selections | smallint default 1 | `0` makes the group optional |
| max_selections | smallint default 1 | `1` is "pick one" |
| position | smallint default 0 | |
| timestamps | | |

**`{prefix}bundle_components`** — a variant inside a bundle, fixed or offered in a group.

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| public_id | ulid unique | referenced from cart line meta |
| bundle_id | FK bundles, cascadeOnDelete | |
| bundle_group_id | FK bundle_groups null, cascadeOnDelete | `null` means always included |
| product_variant_id | FK product_variants, cascadeOnDelete | the component |
| quantity | smallint default 1 | units per bundle unit |
| default | boolean default false | pre-selected inside a group; drives repricing (2.5) |
| position | smallint default 0 | |
| timestamps | | |
| unique (bundle_id, bundle_group_id, product_variant_id) | | |

A bundle with no groups is a **fixed bundle**. A bundle with one or more groups is a **configurable bundle**. Fixed and grouped components can coexist ("always includes the body; choose a lens").

Invariants, enforced by the actions and by a `BundleNesting` exception:

- A component variant is never itself a bundle variant (no nesting).
- A component variant is never the bundle variant.
- A bundle has at least one component, and no more than `lunar.bundles.max_components` (default 25).
- `min_selections <= max_selections <= number of components in the group`.

Deleting a component variant cascades to its `bundle_components` row; the panel "Included in bundles" card (2.8) is the merchant's warning before they do that.

#### 2.3 Models and the variant relation

`Bundle`, `BundleGroup`, `BundleComponent` extend `Lunar\Core\Models\Base` with `HasPublicId`, `HasMacros` and `LogsActivity`; `BundleGroup` adds `HasTranslations`; `BundleComponent` and `BundleGroup` use `InvalidatesRelatedCache` with `cacheInvalidationTargets()` returning `[$this->bundle]`; `Bundle` uses `InvalidatesCache` with `cacheInvalidationTargets()` returning `[$this->variant->product]` and a `BundleInvalidated` event. `ModelManifest::addDirectory(__DIR__.'/Models')` registers all three (morph keys `bundle`, `bundle_group`, `bundle_component`).

```php
class Bundle extends Base
{
    public function variant(): BelongsTo;          // ProductVariant
    public function groups(): HasMany;             // ordered by position
    public function components(): HasMany;         // ordered by position, with group
    public function fixedComponents(): HasMany;    // whereNull('bundle_group_id')
    public function isConfigurable(): bool;

    // Verbs, one-line delegating to the action contracts (spec 0029)
    public function syncComponents(array $components): self;
    public function syncGroups(array $groups): self;
    public function reprice(): self;
    public function resolveSelection(array $meta = []): BundleSelection;
}
```

The package adds the relation the other way with the precedent `table-rate-shipping` uses:

```php
ProductVariant::resolveRelationUsing('bundle', fn (ProductVariant $variant) => $variant->hasOne(Bundle::class));
Product::resolveRelationUsing('bundles', fn (Product $product) => $product->hasManyThrough(Bundle::class, ProductVariant::class));
```

`$variant->bundle` is `null` for an ordinary variant. That is the whole "is this a bundle" test; there is no flag on the variant.

#### 2.4 Actions

Every action implements a contract in `Contracts/Actions/`, exposes `execute()`, injects its collaborators, and is bound in `BundlesServiceProvider::register()`.

| Action | Signature | Notes |
|---|---|---|
| `DefineBundle` | `execute(ProductVariant $variant, BundlePricing $pricing, ?float $discountPercentage = null): Bundle` | Creates or updates the `bundles` row. Throws `BundleNesting` if `$variant` is already a component elsewhere. Reprices. |
| `SyncBundleComponents` | `execute(Bundle $bundle, array $components): Bundle` | `$components` is `list<array{variant: ProductVariant\|int, quantity: int, group?: BundleGroup\|int\|null, default?: bool, position?: int}>`. Upserts and deletes to match. Validates the invariants. Reprices. |
| `SyncBundleGroups` | `execute(Bundle $bundle, array $groups): Bundle` | `list<array{id?: int, name: array<string,string>, min_selections: int, max_selections: int, position?: int}>`. Deleting a group deletes its components. Reprices. |
| `RepriceBundle` | `execute(Bundle $bundle): Bundle` | Materialises price rows (2.5). No-op for `fixed`. |
| `ResolveBundleSelection` | `execute(Bundle $bundle, array $meta = []): BundleSelection` | Turns cart line meta into a validated component list (2.6). Throws `InvalidBundleSelection`. |
| `DeleteBundle` | `execute(Bundle $bundle): void` | Removes the bundle definition. The variant becomes an ordinary variant again; its materialised prices are left in place. |
| `ResolveBundleInventory` | `execute(ProductVariant $variant): VariantInventory` | Decorator over core `ResolvesInventory` (2.7). |

#### 2.5 Pricing

`BundlePricing::Fixed`: the merchant prices the bundle variant like any other variant. The package does nothing.

`BundlePricing::Components`: the bundle's price is the quantity-weighted sum of its components' prices, less `discount_percentage`. `PricingManager::get()` throws before its pipeline runs when a purchasable has no price row in the requested currency, so a derived price cannot be computed at read time. Instead `RepriceBundle` **materialises real `prices` rows** on the bundle variant, and the package keeps them current:

- One base row per currency in which every included component has a base price. A currency any component lacks gets no row, so the bundle is unsellable in that currency and the panel card says which component is missing a price.
- One customer-group row per (currency, customer group) where at least one component has a group price; each component contributes its group price if it has one, otherwise its base price.
- Quantity price breaks on components are ignored. Bundle pricing is per bundle unit.
- `price` is the sum less `discount_percentage`, computed through `PriceCalculator::percentage()` so rounding follows `Currency::decimal_places`. `list_price` is the undiscounted sum when a discount applies, so storefronts show the saving with the machinery they already use for `list_price`.
- For a configurable bundle the rows reflect the **default selection**: fixed components plus each group's `default` components. That is the "from" price on a listing.

Rows are written through the core `Price` model so cache invalidation and search reindexing fire as for any price change. Repricing is triggered by `DefineBundle`, `SyncBundleComponents`, `SyncBundleGroups`, and by two listeners: `RepriceOnComponentPriceChange` (a `Price` saved or deleted whose priceable is a component variant) and `RepriceOnComponentInvalidation` (`ProductInvalidated` for a product with a variant used as a component). `lunar:bundles:reprice` recomputes every `components` bundle, for backfill and after currency or customer-group changes.

Because the rows are ordinary price rows, the panel pricing editor shows them and lets the merchant edit them. The bundle card states that prices in `components` mode are derived and overwritten on reprice. A merchant who wants to hand-tune switches to `fixed`.

A configurable bundle in `components` mode whose selection differs from the default is priced at cart time by `Pipelines/CartLine/PriceBundleSelection`, a `lunar.cart.pipelines.cart_lines` stage appended after `GetUnitPrice` that overwrites `$cartLine->unitPrice` and `unitPriceInclTax` from the resolved selection. `CalculateLines` reads the unit price the pipeline leaves on the line, and `CalculateLineSubtotal` keeps a unit price that is already set, so nothing downstream recomputes it. The stage mirrors `GetUnitPrice`'s currency, quantity and customer-group handling by pricing each component through the `PricingManager` contract, and applies the same `discount_percentage`. Fixed bundles and `fixed`-priced bundles never enter the stage. (`CartLineModifier::calculating()` is not used: core never invokes that hook.)

Tax: the parent line is taxed at the bundle variant's tax class, like any variant. A bundle whose components have different tax rates is taxed at one rate. Mixed-supply apportionment is out of scope (see Open questions).

Discounts: a discount targeting the bundle product or a collection it sits in applies to the bundle line as normal. A discount targeting a component product does **not** apply to bundles containing it; discounts see only the bundle cart line.

#### 2.6 Cart and checkout

A bundle is added to the cart as its variant, the same call as any product:

```php
CartSession::add($bundleVariant, 1);                                    // fixed bundle
CartSession::add($bundleVariant, 1, ['bundle' => [
    'selections' => [
        '01J...GROUP-PUBLIC-ID' => ['01J...COMPONENT-PUBLIC-ID'],
    ],
]]);                                                                    // configurable bundle
```

Cart line identity is purchasable plus a `meta` diff (`GetExistingCartLine`), so two different selections of the same configurable bundle correctly become two lines, and re-adding the same selection increments quantity.

`ResolveBundleSelection` is the single place meta is interpreted:

- Fixed components are always included.
- For each group, `meta.bundle.selections[group public_id]` must list between `min_selections` and `max_selections` component public ids belonging to that group. A group absent from meta with `min_selections = 0` contributes nothing; absent with `min_selections > 0` falls back to the group's `default` components when they satisfy the minimum, otherwise the selection is invalid.
- Unknown groups or components, duplicates, and components from another bundle are invalid.
- Returns a `BundleSelection` value object: `Collection<SelectedComponent>` where `SelectedComponent` is `(ProductVariant $variant, int $quantity, ?BundleGroup $group)`, plus `isDefault(): bool`.

`Validation/CartLine/BundleSelection` is appended to `lunar.cart.validators.add_to_cart` and `update_cart_line` by the service provider (the same config-append pattern `table-rate-shipping` uses for modifiers) and rejects an invalid selection with a validation message. For a bundle line it also checks every selected component with `canBeFulfilledAtQuantity(component.quantity * line.quantity)`, so the cart-time stock check sees the actual selection rather than the default one the inventory seam reports (2.7).

`Pipelines/Order/Creation/CreateBundleComponentLines` is appended to `lunar.orders.pipelines.creation` after `MapDiscountBreakdown`. For every top-level order line whose purchasable is a bundle variant it:

1. Sets `type = 'bundle'` and `requires_fulfilment = false` on the parent line. `requires_shipping` keeps the variant's value so shipping is still required at the order level. `meta.bundle` is left as the cart line wrote it.
2. Deletes the parent's existing component lines (the stage is idempotent for `orderIdToUpdate` re-runs).
3. Resolves the selection from the parent's `meta` and creates one component line per `SelectedComponent`: `parent_line_id`, `purchasable` = the component variant, `type`, `requires_shipping`, `requires_fulfilment`, `description`, `option`, `identifier`, `unit_quantity` snapshotted from the component, `quantity = component.quantity * parent.quantity`, every money column `0`, empty `tax_breakdown`.
4. Writes `meta.allocated_total` on each component line: the parent's `total` distributed across components in proportion to their current unit prices with `PriceCalculator::distribute()`, which is exact and drift-free. This is for revenue-per-SKU reporting only and never feeds a total.

Because component lines exist before `OrderPlaced` fires, the existing listeners do the rest with no bundle awareness: `EnsureInitialFulfilment` claims the components into fulfilments, `SyncStockForOrder` commits component stock through `SyncStockCommitment`'s existing `order_lines` predicate, `AllocateStockForFulfilment` allocates per location, and shipping, returning or cancelling a fulfilment moves component stock. The bundle variant itself is never committed: its parent line is `requires_fulfilment = false`, so `SyncsTrackedStock` skips it.

Spec 0083 (cart-line fulfilment method), if it lands, applies per line: the stage copies the parent's `fulfilment_method` to each component line, and `FulfilmentMethod::supports()` is answered per component. A mixed physical and digital bundle therefore fulfils correctly without the bundle variant having to answer for both.

The order-line snapshot is authoritative (spec 0045). The component lines are the record of what was sold; the `Bundle` row can change afterwards without touching placed orders.

#### 2.7 Stock

`ResolveBundleInventory` decorates core `ResolvesInventory`:

```php
public function execute(ProductVariant $variant): VariantInventory
{
    $bundle = $variant->bundle;   // eager-loaded where the caller can; the relation is cheap otherwise

    if (! $bundle) {
        return $this->inner->execute($variant);
    }

    // Fixed components plus, per group, the best-stocked option: "available" means
    // at least one valid configuration can be sold.
    $candidates = $this->candidates($bundle);

    return new VariantInventory(
        available: $candidates->min(fn ($c) => intdiv($this->inner->execute($c->variant)->available, $c->quantity)),
        unlimited: $candidates->every(fn ($c) => $this->inner->execute($c->variant)->unlimited),
    );
}
```

So `$bundleVariant->getTotalInventory()` reports how many complete bundles can be sold, `CartLineStock` enforces it without change, spec 0082's order-creation re-validation enforces it at placement, and a storefront stock badge on a bundle is right without knowing bundles exist. The bundle variant's own `stock_*` columns are ignored and stay at zero; the panel inventory card on a bundle variant still shows them (see Open questions).

For configurable bundles the seam answers for the best case, and `Validation/CartLine/BundleSelection` (2.6) answers exactly for the chosen selection.

`ProductVariant implements TracksStock` already; `syncStockCommitment()` on a bundle variant recomputes from the (absent) order lines pointing at it and stays at zero. No override is needed.

#### 2.8 Panel

`Panel/BundlesSectionExtension extends SectionExtension` with `extends(): 'catalog'`, reusing `CatalogSection::PRODUCTS_PERMISSION`. Bundles are products, so there is no new nav item, no new permission, no new search source and no draftable: the product's own page is where a bundle is edited, and product search already finds it.

Slots, all lazily loading their data through the extension's JSON routes so ordinary products pay nothing:

| Zone | Component | Purpose |
|---|---|---|
| `products.edit:variants:after` | `bundles::BundleCard` | Single-variant product: the full bundle editor inline. Multi-variant product: a summary per variant linking to the variant page. |
| `products.variants.edit:main:after` | `bundles::BundleCard` | The bundle editor for that variant. |
| `products.edit:sidebar:after` | `bundles::IncludedInBundlesCard` | Bundles that include any of this product's variants, each linking to its product. Hidden when empty. |

The bundle editor: a "Sell as a bundle" toggle (creates or deletes the definition), pricing mode and discount percentage, the fixed component list (variant picker, quantity, reorder, remove), and, in a later slice, groups with their options, min/max selections and defaults. It saves through its own routes immediately, the way media and URL sub-resources save on the product page, rather than through the edit draft; the product's own drafted fields are untouched.

Variant picking reuses the exported `TargetPickerDialog` and `TargetChipList` against a new endpoint, `GET bundles/search-variants?q=`, returning `id, public_id, sku, product name, option, thumbnail, is_bundle` and excluding bundle variants so nesting is impossible from the UI as well as the actions.

Routes (inside `Route::middleware('can:'.CatalogSection::PRODUCTS_PERMISSION)`), all named `panel.bundles.*`:

```
GET    bundles/search-variants
GET    bundles/variants/{variant}                 summary JSON for the cards
PUT    bundles/variants/{variant}                 define (pricing, discount) or create
DELETE bundles/variants/{variant}                 remove the definition
PUT    bundles/variants/{variant}/components      sync
PUT    bundles/variants/{variant}/groups          sync
GET    bundles/products/{product}/included-in     JSON for the sidebar card
```

Table extension on `products.index`: a `Bundle` badge column (hidden by default) and a "Bundles only" filter.

Order view: covered by the first-party changes in 1.1. The extension adds nothing.

The card needs a main-column card wrapper; `Section.vue` is internal to the panel today. Slice 6 exports it from `ui.ts` (and `index.js`, `dist/ui.d.ts`) so add-ons stop reimplementing it.

Translations: `resources/lang/{16 locales}/bundles.php`, served through `langNamespaces(): ['bundles']`. Vue strings use vue-i18n `{placeholder}` syntax.

Filament admin (`lunarphp/admin`): none in this spec. Filament users get the core behaviour and the storefront surface; a relation manager is a follow-on.

#### 2.9 Configuration (`lunar.bundles`)

```php
return [
    'max_components' => 25,   // per bundle, groups included
];
```

Values only. Every class seam is a container binding.

#### 2.10 Storefront surface (headless)

```php
$variant->bundle;                                   // ?Bundle
$variant->bundle?->components;                      // with ->variant, ->group
$variant->bundle?->groups;                          // with ->components
$variant->pricing()->get();                         // works: the rows are real (components mode) or the merchant's (fixed)
$variant->getTotalInventory();                      // derived from components
$variant->bundle?->resolveSelection($meta);         // BundleSelection for a proposed selection; throws InvalidBundleSelection
CartSession::add($variant, $qty, ['bundle' => ['selections' => [...]]]);
$order->lines()->topLevel()->with('components.purchasable');
```

Search: bundles are products, so they are indexed and found as products. The package adds nothing to the search index.

Cache: editing a bundle's components or groups invalidates the bundle's product through `InvalidatesRelatedCache`. A change to a component product (`ProductInvalidated`) is fanned out by `Listeners/InvalidateContainingBundles` to every bundle product that includes one of its variants, so a storefront page cached against the bundle product refreshes when a component's price or stock changes.

## Alternatives considered

- **A standalone `Bundle` purchasable model.** Cleanest package boundary, but the core assumes cart and order line purchasables are variants in at least these places: `CartLine::taxClass()` (a `HasOneThrough` on `purchasable_type`), `CartLineQuantity` (reads `min_quantity` and `quantity_increment` as raw properties), `CartLineAvailability` (short-circuits non-variants to `isPurchasable()`), `config('lunar.cart.eager_load')` and `CalculateLines::loadMissing()` (variant relation paths under `preventLazyLoading`), `TargetsCartLines` and `BuyXGetY` (dereference `$line->purchasable->product` unguarded), and `ValueObjects\Cart\FreeItem` (typed `ProductVariant`). Storefronts list products; a separate model would be invisible in collections, brand pages and search unless every storefront learned about it. The panel's media, attribute, URL, availability and pricing editors are not exported to add-ons, so a bundle edit page would reimplement the product page. Rejected.
- **One order line with the components snapshotted in `meta`.** No schema change, but `SyncStockCommitment` counts `order_lines` by variant morph and would see zero commitment for components; JSON-path queries to fix that differ across MySQL, Postgres and SQLite; fulfilment lines would point at the bundle with no parts to pick; per-component location allocation and returns would need a parallel implementation. Rejected.
- **Component lines carrying allocated prices and no parent line.** Totals stay right and Shopify's order model looks like this, but the customer's contract ("a kit at this price") is spread across lines, refunds become per-component, discount breakdown mapping breaks (component lines are not the image of a cart line), and a standalone line for the same variant in the same cart collides on identity. The allocation is kept, as `meta.allocated_total` on the components, for reporting. Rejected as the primary shape.
- **Runtime derived pricing through `lunar.pricing.pipelines`.** `PricingManager::get()` throws `MissingCurrencyPriceException` before its pipeline runs, so the bundle variant would still need placeholder rows; and Scout price sorting, the panel price column and any storefront listing read price rows, not the manager. Materialised rows make the derived price real everywhere. Rejected.
- **Cart validators instead of the inventory seam.** A `BundleStock` validator on add and update covers the cart, but a storefront stock badge, the panel and spec 0082's placement re-validation would each need their own bundle awareness. One seam behind the two contract methods makes every caller right. The validator survives only for the selection-specific check.
- **Do nothing; use `BuyXGetY`.** It is a cart-time promotion over independent lines, not a sellable composite: no single product to list, no derived stock, no kit price. Rejected.

## Migration impact

- **Database**: `order_lines.parent_line_id` is added to the core baseline migration (v2 is in alpha; no change migration). Three new tables from `bundles`, prefixed through `Migration::$prefix`, types chosen for MySQL 8, Postgres and SQLite.
- **Public contract surface**: `OrderLine` gains `parent()`, `components()`, `isComponent()` and the `topLevel` scope; `ProductVariant::getTotalInventory()` and `canBeFulfilledAtQuantity()` keep their signatures and delegate to the new `ResolvesInventory` action contract; `VariantInventory` is a new DTO. Three order-creation stages skip component lines. Non-breaking. Code that iterates `$order->lines` for display sees component lines with zero money columns and should switch to `topLevel()`; the docs and `WHATS-NEW` call this out.
- **Upgrade path for v1.x**: none. v1 has no bundles; `lunarphp/upgrade` needs no rule. The `topLevel()` guidance is documented for anyone porting a v1 storefront.
- **Translation / locale impact**: new `bundles` lang namespace, all 16 locales shipped with translated values, per the monorepo rule.
- **Filament / admin impact**: none. Component-line nesting in the Filament order view is a follow-on.
- **Panel**: first-party changes in `OrderShowController`, `FulfilmentLineRow.vue`, the refund composer and the non-fulfillable lines card; `Section` exported from `ui.ts`.
- **Queues**: repricing runs synchronously on the triggering event. A store with many bundles per component can move it to the queue in a follow-on.

## Docs PR (`lunarphp/docs`)

1. **`2.x/addons/bundles.mdx`** (Add-ons, General): what a bundle is and is not; installation; fixed and configurable bundles; pricing modes and how materialised prices behave; stock derivation; how the order records components and why; the storefront calls above with a worked add-to-cart for a configurable bundle; the reprice command.
2. **`2.x/reference/orders.mdx`**: "Component lines" section — `parent_line_id`, `topLevel()`, `components()`, what money a component line carries, `meta.allocated_total`.
3. **`2.x/reference/inventory.mdx`**: the `ResolvesInventory` seam and `VariantInventory`.
4. **`2.x/admin/bundles.mdx`** (Admin Panel tab): the bundle card, the included-in card, the products table badge and filter, what the inventory card means on a bundle variant.
5. **`2.x/guides/cart.mdx`**: a short "Selling bundles" subsection.
6. **`logs/flight-plan.mdx`**: entry for the feature.

## Open questions

1. **Mixed-rate tax.** The parent line is taxed at the bundle variant's tax class. Apportioning across components at their own rates (a UK "mixed supply") is a real requirement for some merchants. Proposed: out of scope here, tracked as a follow-on that could use `meta.allocated_total` as the apportionment base. Owner: Glenn.
2. **Inventory card on a bundle variant.** The first-party inventory card shows the bundle variant's own (meaningless) stock. Options: leave it and let the bundle card explain; add a `products.variants.edit:inventory` zone the extension can replace; or have the card read `getTotalInventory()`. Proposed: leave it in this spec, add the zone in a panel follow-on. Owner: Glenn.
3. **`fixed` pricing with an automatic saving display.** In `fixed` mode the merchant sets `list_price` by hand. Should the package offer "show the component sum as the list price" as a toggle? Proposed: no; `components` mode with a percentage covers the common case. Owner: Glenn.
4. **Per-component refunds.** Refunds are parent-only. A merchant refunding one damaged part of a kit refunds an amount against the parent line. Confirm this is acceptable for v1. Owner: Glenn.
5. **Repricing on the queue.** Synchronous repricing is simplest and correct; a component variant used in hundreds of bundles makes a price save slow. Decide the threshold, or ship a `lunar.bundles.reprice_queue` connection value from the start. Owner: Glenn.

## References

- `packages/core/src/Contracts/TracksStock.php` — names a bundle as a motivating custom purchasable
- [[0030-fulfillable-order-lines]] — `type` is display-only; fulfilment keys off `requires_fulfilment`
- [[0038-inventory-fundamentals]] — commitment recomputed from the order book; `SyncsTrackedStock`
- [[0045-optional-purchasables-and-shipping-de-morph]] — the order-line snapshot is authoritative
- [[0046-public-id-external-addressing]] — group and component public ids in cart meta
- [[0057-panel-products-section]] and [[0067-panel-fulfilment-centric-order-view]] — the zones and order view this extends
- [[0082-selling-policy-rework]] (draft) — changes `ResolveInventory`'s formula, not the seam
- [[0083-cart-line-fulfilment-method]] (draft) — per-line fulfilment method copied to component lines
- [[0084-search-relevance]] — the add-on packaging this package copies
- `packages/panel-addon-example` — the panel add-on pattern

## Implementation plan

- [ ] Slice 1 — core: `order_lines.parent_line_id`, `OrderLine` relations and `topLevel` scope, creation stages skip component lines, parent-only `refundableQuantity()`, tests
- [ ] Slice 2 — core: `ResolvesInventory` contract, `VariantInventory`, `ResolveInventory` action, `ProductVariant` delegation, `ActionServiceProvider` binding, tests
- [ ] Slice 3 — `bundles`: skeleton and monorepo wiring, migrations, models, factories, `ProductVariant::bundle` relation, define/sync/delete actions and verbs, invariants, cache invalidation, headless tests
- [ ] Slice 4 — `bundles`: `ResolveBundleInventory` decorator, `ResolveBundleSelection`, `BundleSelection` validator, `CreateBundleComponentLines`, end-to-end cart → order → fulfilment → stock tests including mixed physical and digital
- [ ] Slice 5 — `bundles`: `RepriceBundle`, materialised price rows, listeners, `PriceBundleSelection` stage, `lunar:bundles:reprice`, tests
- [ ] Slice 6 — panel (first party): component-line nesting in the order view and refund composer, `Section` exported from `ui.ts`
- [ ] Slice 7 — `bundles` panel: section extension, routes, `BundleCard` for fixed bundles, variant search endpoint, `IncludedInBundlesCard`, products table badge and filter, 16 locales, panel tests, JS build and CI
- [ ] Slice 8 — `bundles` panel: configurable groups editor
- [ ] Slice 9 — demo data: two bundles in the catalogue generator; docs PR (`lunarphp/docs`)
