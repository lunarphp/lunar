# 0057 — Panel Products section

- Status: implemented
- Author: Glenn Jacobs
- Created: 2026-07-21
- TODO item: Panel Products section — list KPIs, product editing with options/variant builder, variant editing, catalog nav restructure (spec 0057)

## Problem

Products is the panel's flagship catalog surface and the screen every prior section was
built toward: Brands (spec 0052) introduced the shared editing surfaces (media,
attributes, URL slugs, collection picker), Collections (spec 0055) added the product
picker and availability card, and Product Types (spec 0056) delivered the attribute
mapping the product edit screen renders. The design prototype (`lunar-v2-ui`:
`ProductsList.vue`, `ProductEdit.vue`, `VariantEdit.vue` plus the option/variant-builder
components around `useProductVariantBuilder.js`) defines three screens — products list,
product edit, variant edit — none of which exist in the panel.

The product edit screen is also the panel's most important extension surface: spec 0049
deliberately shipped no SEO section, promising it instead as the canonical example of an
add-on injecting into a slot zone. That example needs the zones to exist.

Core support is strong but has gaps:

- **No product or variant CRUD action seams.** Product writes happen as plain Eloquent
  in Filament. Core has `UpdateProductStatus` and `DuplicateProduct`, and
  `MapVariantsToProductOptions` is a compute-only permutation helper shaped for the
  Filament widget — nothing creates, updates or deletes a product or variant, and
  nothing persists a variant generation.
- **No variant availability toggle.** The prototype enables/disables individual
  variants (side card, table filter, bulk bar); `product_variants` has no such column —
  a variant is sellable whenever its product is published and stock allows.
- **No delete guards.** `Product::hasOrderHistory()` exists but nothing enforces it;
  `ProductVariant` has no equivalent, and deleting the last variant of a product is
  unguarded.

Several prototype-isms need settling against core:

- The prototype shows a **product-level SKU** (Basics field, list column, page-header
  chip); core SKUs live on variants only.
- The prototype's list has a **"Type" column showing Stock/Digital** — a product kind
  core does not model — and a **Reviews column/filter/bulk action**; Reviews screens
  are explicitly out of panel scope (spec 0049, add-on territory).
- The prototype's status vocabulary is **draft/published/archived** for products —
  matching core's `ProductState` for once — but its Status card offers a "Schedule
  publish" date, which core has no column for (channel scheduling covers go-live).
- The prototype **requires a brand before publishing**; core's `brand_id` is nullable.
- The prototype's inventory card toggles between a single stock field and a
  per-location table behind an "Inventory module" tweak; core (spec 0038) always has
  per-location `StockLevel` rows plus rollup columns on the variant.
- The prototype caps attached options at **3 per product**; the
  `product_product_option` pivot has no cap.

## Proposal

Products list/create/edit screens plus a variant edit screen in the existing
`CatalogSection`, matching the design prototype and following the established
architecture (core action delegation, edit drafts, table extensions, immediate
sub-resources). The catalog navigation is restructured to the prototype's shape:
a **Products** entry first in the group with contextual children **All products** and
**Product types** — moving the Product Types nav item from its current top-level slot
after Collections. The product edit page ships a full set of named slot zones and the
panel's first first-party `PageAction`, making it the reference extension surface.

### Core additions

**Variant `enabled` flag.** `enabled` boolean column on the `product_variants` baseline
migration (alpha fold-in), default `true`, indexed. Cast on the model, with
`scopeEnabled()`. `ProductVariant::isPurchasable()` honours it (a disabled variant is
never purchasable regardless of product status or stock), and storefront option/variant
resolution treats disabled variants as absent. This backs the prototype's per-variant
toggle — the "temporarily pull one size" affordance — and the variants table's
enable/disable filter and bulk bar. Upgrade package backfills `true` for v1 migrators.
A plain boolean, not a state machine: two states, no transitions worth gating, and the
product already carries the three-state lifecycle.

**Variant order-history guard.** `ProductVariant::hasOrderHistory()` — the
`Product::hasOrderHistory()` query scoped to one variant (order lines whose purchasable
morph points at it). This is the "locked" concept the prototype's regenerate dialog and
delete flows hang off.

**Product actions.** New contracts in `Contracts/Actions/Products/` with
implementations in `Actions/Products/`, registered in `ActionServiceProvider`:

- `CreatesProduct` — creates from validated attributes (translated name, required
  `product_type_id`, status, optional brand), then creates the product's **initial
  variant**: null SKU, `tax_class_id` pre-filled from the type's `default_tax_class_id`
  (the consumer spec 0056 promised) falling back to the default tax class, core column
  defaults elsewhere. Every product has at least one variant from birth — the invariant
  the simple/multi shape derivation below relies on.
- `UpdatesProduct` — the draft-commit seam: fields (name, translated descriptions,
  status, `product_type_id`, `brand_id`), attribute data, tags sync, `collections()`
  sync, and availability pivot sync (channels via `scheduleChannel`, customer groups
  including the product pivot's extra `purchasable` flag).
- `DeletesProduct` — refuses (new `ProductActionException`) while
  `hasOrderHistory()` — the archive-not-delete rule the docblock already states —
  with the guard also on the model's `deleting` hook (the spec 0052 hardening).
  Deleting cleans up variants and their prices through the same guarded path.

**Variant actions.** New contracts in `Contracts/Actions/Products/` (variants are not
a top-level resource; they live under the product namespace like the stock actions):

- `UpdatesProductVariant` — fields (sku, gtin, mpn, ean, tax_class_id, tax_ref,
  shippable, dimension fields, unit_quantity, min_quantity, quantity_increment,
  backorder, selling_policy, enabled) and attribute data. The variant draft-commit seam.
- `DeletesProductVariant` — refuses while the variant `hasOrderHistory()` or is the
  product's **last variant** (products always keep one); `deleting`-hook guard included.
- `GeneratesProductVariants` — the persistence seam the prototype's builder needs.
  `execute(Product $product, array $optionSelections)` where each selection is
  `{product_option_id, value_ids[]}` in display order. It syncs the
  `product_product_option` pivot (position from array order), computes the pending
  combination set, and diffs it against existing variants by option-value signature
  into **keep / add / remove**: kept variants are untouched (their pricing, stock and
  identifiers survive); added variants are created with defaults copied from the
  product's first variant (tax class, shippable, selling policy, unit/min/increment
  quantities), zero stock, base prices cloned at the first variant's amounts, values
  attached, and an SKU suggested from the product's default URL slug plus the value
  names (uppercased, uniquified); removed variants are deleted through
  `DeletesProductVariant` — the whole generation **refuses up front** if any removal
  has order history, mirroring the prototype's locked-rows block. An empty selection
  collapses to the simple shape: options detached, every variant but the first removed
  (same guard). Exclusive options (rows the merchant creates inline, `shared = false`)
  are created/updated/pruned as part of the sync; shared options are only ever
  attached/detached. Validation caps selections at 3 options (the prototype's cap — a
  guard against combinatorial explosion, enforced in the action so every surface gets
  it) and requires every attached option to keep at least one selected value.
  `MapVariantsToProductOptions` stays untouched for the Filament widget.

No new model verbs: like the brands/collections/product-types write seams, these are
admin-facing operations invoked through the contracts.

### Navigation restructure

`CatalogSection::navigation()` changes to the prototype's shape:

- New `products` item — icon `box`, route `panel.products.index`, permission
  `catalog:manage-products`, positioned first in the catalog group
  (`Position::before('brands')`). It carries two children (the `NavigationItem`
  children mechanism already exists and renders contextually when the parent is
  active, exactly as the prototype's sub-nav): `all-products` → `panel.products.index`
  (exact match) and `product-types` → `panel.product-types.index`.
- The existing top-level `product-types` item is removed — this is the nav move: the
  prototype never shows Product types as a peer of Brands/Collections; it lives under
  Products. Resulting group order: **Products** (All products / Product types),
  Brands, Collections.

### Panel server side

**`CatalogSection`** grows the products surface, permission
`catalog:manage-products` throughout (the existing handle; no new permission):

- `routes()` — prefix `products`, names `panel.products.*`:
  - `index`, `create`, `store`, `bulk-status` (`POST /bulk/status/{status}` whereIn
    published/draft), `edit`, `update`, `destroy`, the standard `draft.*` trio.
  - Under `Route::scopeBindings()`: `media.*` and `urls.*` (the brand controller
    shapes, reused), `prices.store|update|destroy`, `associations.store|destroy`,
    `options.generate` (`POST /{product}/options/generate` — the
    `GeneratesProductVariants` endpoint; an empty selection is the collapse path),
    and the variant surface: `variants.edit`, `variants.update`, `variants.destroy`,
    the variant `variants.draft.*` trio (the shared `EditDraftController` resolves the
    route-bound variant), `variants.media.sync` (ordered id list from the product's
    media pool), `variants.stock.adjust` (per-location on-hand set, written as an
    adjustment movement through the existing `AdjustStock` verb), and
    `variants.bulk` (`POST /{product}/variants/bulk` — enable / disable / destroy /
    set base price / set stock at the default location, over a variant id selection).
  - `catalog/product-options/search` (`panel.catalog.product-options.search`) —
    shared options with their values for the Add-option menu, alongside the existing
    `products.search`/`collections.search` endpoints.
- `tableExtensions()` — `['products.index' => ProductsTableExtension::class]`.
- `draftables()` — adds `ProductDraftResource::class` and
  `ProductVariantDraftResource::class`.
- `pageActions()` — `'products.edit' => [DuplicateProductPageAction::class]`: the
  panel's first first-party page action, delegating to core's existing
  `DuplicateProduct` and redirecting to the copy's edit page. It dogfoods the
  `PageAction` extension point on the page add-ons care most about.

**Controllers** (`Http/Controllers/Products/`), writes delegated to core actions:

- `ProductIndexController` — columns: thumbnail + name, status, brand, SKU (first
  variant's SKU with a "+N more" badge from the variant count), stock (sum of
  `stock_available` across variants), type (product type name), tags. Search matches
  name, variant SKUs and URL slugs. Filters: status, brand, product type, tag, and an
  out-of-stock flag. Sort allow-list: name, created_at, stock (default `created_at`
  desc). `paginate(15)`, rows shaped with `edit_url` and `_actions` via the
  table-extension resolver. **KPI strip** (the customers pattern — cached 5 minutes,
  dismissal persisted in `localStorage`): total products, published, drafts, out of
  stock; each card click applies the matching filter, per the prototype.
- `ProductCreateController` — minimal create: translated name, product type (combobox
  over **active** types — the spec 0056 gating semantics), status segmented control
  (default `draft`, the core default). Store calls `CreatesProduct`, redirects to edit.
- `ProductEditController` — renders `products/Edit` with: product fields, `draft`,
  attribute schema + values (product morph), media, `urls` + languages, brand options,
  selected collections, tags, availability rows (channels + customer groups with the
  product pivot's `purchasable` flag), the attached options with their resolved values
  and each variant's value signature (the builder payload), variant rows (id, label
  from its values, sku, base price, `stock_available`, enabled, order-history lock,
  edit URL), associations grouped by type, product types for the type card (active
  plus the current one), activity, and the route map. `update`/`destroy` as brands;
  destroy surfaces the order-history guard as a flash error suggesting Archive.
- `ProductBulkStatusController` — the brands shape over published/draft.
- `ProductPriceController` — `store`/`update`/`destroy` for price rows through
  `HasPrices` (validated: currency, optional customer group, `min_quantity`,
  price + nullable `list_price` in minor units). Immediate sub-resource.
- `ProductAssociationController` — attach/detach through the existing
  `Product::associate()`/`dissociate()` verbs; type validated against the
  `ProductAssociation` enum. The picker is the spec 0055 `ProductPickerDialog`.
- `ProductOptionsController` — the `options.generate` endpoint: validates the
  selection shape (shared option ids exist and are `shared`; exclusive rows carry
  name + values; at most 3 options; every option at least 1 value), delegates to
  `GeneratesProductVariants`, flashes the keep/add/remove counts.
- `ProductVariantController` — `edit` renders `products/VariantEdit` (variant fields,
  draft, prices, per-location stock levels + aggregate, the product's media pool with
  the variant's selection, variant attribute schema + values, sibling list for
  prev/next navigation, activity); `update` delegates to `UpdatesProductVariant`;
  `destroy` to `DeletesProductVariant` (guard → flash error).
- `ProductVariantMediaController` — `sync`: ordered media-id list from the product's
  pool written to the `media_product_variant` pivot (first is primary).
- `ProductVariantStockController` — per-location on-hand set via `adjustStock`
  (delta computed server-side, `adjustment` movement type); returns the refreshed
  levels + rollup.
- `ProductVariantBulkController` — the variants-table bulk bar: enable/disable
  (column update), destroy (per-variant guards, locked rows skipped and reported),
  set base price (upsert the default-currency base price row per variant), set stock
  (on-hand at the default location, as adjustments).

**Requests** (`Http/Requests/Products/`): `ProductRequest` (name required translated
map, status in-state-list, `product_type_id` exists + active-or-current, `brand_id`
nullable exists — **optional**, resolving the prototype's required-for-publish as a
prototype-ism core does not enforce, translated description maps, attribute values
against the schema, `collection_ids`, `tags` string array, availability row shapes
including `purchasable`, and — simple shape only — `variant:*` fields validated by the
variant rules), `ProductVariantRequest` (identifier/tax/shipping/ordering fields,
`selling_policy` enum, `enabled` boolean, variant attribute values; SKU unique,
nullable), `ProductOptionsGenerateRequest`, `ProductPriceRequest`,
`ProductAssociationRequest`, `ProductVariantStockRequest`, `ProductVariantBulkRequest`,
plus the brand-shaped media/URL requests.

**`ProductDraftResource`** — draftable fields: `name`, `status`, `product_type_id`,
`brand_id`, `short_description`, `description`, `tags`, `collection_ids` (normalised
sorted), per-attribute keys (`attribute:{handle}`), per-row availability keys
(`channel:{id}`, `customer_group:{id}` — reusing `AvailabilitySchema`, whose product
rows carry the extra `purchasable` flag). **Simple shape only**, it additionally
accepts `variant:{field}` keys targeting the product's sole variant (identifiers, tax,
shipping, ordering quantities, selling policy, variant `attribute:{handle}` values as
`variant:attribute:{handle}`) so the collapsed layout keeps a single save cluster;
`commit()` dispatches `UpdatesProduct` plus — when variant keys are present —
`UpdatesProductVariant` on the sole variant, and `rules()` rejects `variant:*` keys
while the product has multiple variants. Media, URLs, prices, associations, options
and stock are immediate sub-resources outside the draft.

**`ProductVariantDraftResource`** — the same variant field set for the variant edit
page. `rules()` delegates to `ProductVariantRequest`; `commit()` to
`UpdatesProductVariant`.

**`ProductsTableExtension`** — `EditProductAction`, `DeleteProductAction` (URL `null`
while the product has order history — the ProductOptions precedent, server guard as
backstop; confirmation copy points at Archive), and `SetProductsPublishedBulkAction` /
`SetProductsDraftBulkAction`.

### Pages

`pages/products/{Index,Create,Edit,VariantEdit}.vue`, standard scaffold
(`PanelLayout` + `Breadcrumbs` + `PageHeader` + `PageZone`, enforced by
`PageScaffoldTest`):

- **Index** — hero-style header via `PageHeader` (box icon, description line,
  "New product" primary action), dismissible KPI strip with click-to-filter, toolbar
  (debounced search, brand / product type / status / tag `FilterDropdown`s, result
  count, "Show KPIs" restore button), `BulkActionsToolbar` (publish / draft),
  `DataTable`, `Pagination`, `PageEmpty`. The prototype's Reviews column and filter,
  "Request reviews" bulk action, Import button and Stock/Digital "Type" column are not
  ported (Reviews is add-on territory — and the reviews column is the worked example
  of an add-on table-extension column; the Type column shows the product type name
  instead, which core actually models).
- **Create** — name, product type combobox, status segmented control; posts to
  `store`, redirects to edit.
- **Edit** — two-column. Main, in prototype order: **Basics** (translated name,
  translated short description with character hint, `RichTextEditor` description —
  the SKU input appears only in the simple shape, bound to `variant:sku`),
  **MediaManager**, **AttributeFields** (product attributes, with the "inherited
  from {type}" link to the product type's edit page), the **variant shape toggle**
  (segmented control — see below), then per shape: *multi* — **ProductOptionsBuilder**
  + **VariantsTable**; *simple* — the sole variant's cards inline (**PricingEditor**,
  **InventoryCard**, **ShippingCard**, **IdentifiersCard**, **TaxCard**, variant
  `AttributeFields` when the type maps variant attributes); then **Associations**
  (three groups — Related mapping to core's `alternate`, Cross-sell, Upsell — each a
  product-card list with the `ProductPickerDialog`) and **UrlSlugs**. Sidebar:
  **Status** (`StatusSegmentedControl`, three states, drafted; no schedule field —
  channel scheduling in the availability card covers go-live), **AvailabilityCard**
  (extended with the per-customer-group `purchasable` pill for products),
  **Product type** card (combobox over active types + hint that changing it swaps the
  attribute fields; drafted — attribute values not mapped by the new type stay in
  `attribute_data`, simply unrendered, the core behaviour), **Organization** (brand
  combobox — optional, new shared **TagsInput** chip editor, `CollectionPicker`),
  **Activity** (`ActivityTimeline`). Save cluster is `DraftActions` +
  `DraftConflictDialog` via `useEditDraft`.
- **VariantEdit** — breadcrumb Catalog / Products / {product} / {variant};
  `PageHeader` shows the variant label (its value names), enabled badge, SKU, parent
  product link and "Variant N of M" with prev/next navigation buttons. Main:
  `PricingEditor`, **VariantMediaPicker** (select + order from the product's media
  pool; first is hero), `InventoryCard` (per-location), `ShippingCard`,
  `IdentifiersCard`, `TaxCard`, variant `AttributeFields`. Sidebar: **Variant
  status** (enabled toggle, drafted), **Variant options** (read-only value chips +
  "manage options" link back to the product), Activity. Own draft via
  `ProductVariantDraftResource`. The prototype's FIFO cost/valuation section is
  accounting-module territory — out of scope.

**Shape derivation.** The simple/multi toggle is **derived state, not a column**:
multi when the product has attached options or more than one variant, simple
otherwise. Switching simple→multi just reveals the options builder (nothing persists
until generate). Switching multi→simple opens the prototype's confirm dialog ("delete
N variants, keep the first") and posts an empty selection to `options.generate`; the
switch is disabled with the lock tooltip while any non-first variant has order
history.

### New components

- **ProductOptionsBuilder** (`ProductOptionsBuilder.vue` + `OptionRow`,
  `SharedValuePicker`, `ExclusiveValueEditor`, `AddOptionMenu`,
  `VariantCountPreview`, `DriftBanner`, `RegenerateDialog`) — the prototype's builder
  wholesale: attached options as sortable rows (shared rows pick values from the
  canonical option; exclusive rows edit their own name/values inline), an Add-option
  menu listing shared options from Settings (via the search endpoint) plus "create
  exclusive option", live combination-count preview with the underfilled warning,
  and client-side drift detection against the loaded variants' value signatures. The
  panel is visible when no variants exist, when drift exists, or when expanded from
  the variants table; Generate posts the selection to `options.generate`.
  `RegenerateDialog` shows the keep/add/remove lists computed client-side (the server
  recomputes authoritatively) and blocks while a removal is locked by order history.
  Option edits before Generate are deliberately client-side only — abandoning the
  page abandons the pending selection, exactly like the prototype's reset affordance.
- **VariantsTable** — collapsible table of variant rows (thumbnail, value-label link
  to the variant edit page, SKU, base price, available stock, enabled state), filter
  select (all/enabled/disabled/out of stock), selection checkboxes and the bulk bar
  (enable, disable, set price, set stock, delete — locked rows skipped with a
  notice), stale-row dimming while drift exists.
- **PricingEditor** — the prototype's pricing card on core's price shape: one base
  row per enabled currency (price + nullable compare-at `list_price`), a
  customer-group price table, and a tier table (`min_quantity` > 1, optional customer
  group). Rows persist immediately through the price endpoints (debounced upserts,
  the UrlSlugs pattern) — pricing rows have row identity, and tier rows have none
  until saved, which is exactly the shape drafts merge badly (see Alternatives).
  Amounts convert to/from minor units by the currency's decimal places.
- **InventoryCard** — selling-policy segmented control (the three
  `SellingPolicy` values with the prototype's explanatory copy) and ordering fields
  (backorder, unit/min/increment quantities) as drafted fields, plus the
  **per-location stock table** (location, available, on hand, committed, incoming
  from `StockLevel` rows with the aggregate rollup) where on-hand is inline-editable,
  posting immediately to `variants.stock.adjust`. With a single location the table
  collapses to one row — no module toggle; spec 0038 made locations always-on.
- **ShippingCard / IdentifiersCard / TaxCard** — small drafted-field cards: shippable
  toggle + dimension value/unit pairs; sku/gtin/mpn/ean; tax class select + tax ref.
- **TagsInput** — chip editor (Enter/comma to add, click to remove) over the drafted
  `tags` array; shared component, first consumer.
- **VariantMediaPicker** — the product-pool selection grid with ordering.
- **AvailabilityCard** gains an optional per-row `purchasable` control (products
  only — collections pass nothing and render unchanged).

None of the new components are exported on `ui.ts`; promotion of the now-proven
catalog surfaces to the add-on API remains a deliberate follow-up (the spec 0052
policy).

### Extension surface — slots and the SEO example

Every page carries the standard `main:before`/`main:after` zones. The product edit
page adds the named zones that make it the reference surface, each a `PageZone` at a
meaningful seam:

- `products.edit:main:before` / `products.edit:main:after`
- `products.edit:content:after` — after the Basics/Media/Attributes cluster, before
  the variants block (where a content-adjacent card like SEO naturally sits)
- `products.edit:variants:after` — after the options/variants cluster (or the
  sole-variant cards in the simple shape)
- `products.edit:sidebar:before` / `products.edit:sidebar:after`

The variant edit page carries `products.variants.edit:main:before|after` and
`:sidebar:after`. Fulfilling spec 0049's promise, the example add-on
(`packages/panel-addon-example`) gains an **SEO card** registered into
`products.edit:content:after` — title/description inputs with a search-result
preview, the prototype's `Seo.vue` reduced to a demonstration — and `TMP-DOCS.md`
documents it as the canonical slot walkthrough.

### Translations

`products.php` grows the section's strings (list, edit, options builder, variants,
associations, shipping/identifiers/tax cards); new shared `pricing.php` group (the
pricing editor's strings — the later Discounts work reuses them); `availability.php`
gains the purchasable-row strings; `nav.php` gains `products` and `all_products`.
English first, mirrored across all 16 locales, landing with the slice that introduces
each group.

### Testing

- **Pest (`tests/panel/Feature/Products/`)**: index (render, KPI payload + caching,
  search by name/SKU/slug, each filter, out-of-stock flag, sort allow-list +
  fallback, pagination, row shaping incl. first-SKU/+N and stock sum, delete action
  omitted with order history), create (active-type gating, initial variant with
  type-default tax class), edit props (schema, builder payload, variant rows with
  lock flags, availability incl. purchasable, associations), product draft lifecycle
  (scalar, attribute, `collection_ids`, `tags`, availability row, and the simple
  shape's `variant:*` keys — accepted with one variant, rejected with several,
  committed through both actions), bulk status, destroy (guarded vs clean), prices
  (upsert per currency/group/tier, minor-unit conversion, delete), associations
  (attach/detach, type validation), options generate (pivot sync + positions,
  keep/add/remove persistence, defaults copied onto added variants, SKU suggestion,
  locked-removal refusal, 3-option and empty-value caps, exclusive option
  create/prune, collapse path), variant edit props, variant draft lifecycle, variant
  update/destroy guards (order history, last variant), variant media sync, stock
  adjust (movement written, rollup refreshed), variant bulk ops (locked rows skipped),
  duplicate page action, permission gating throughout.
- **Pest (`tests/panel/Unit/`)**: navigation restructure — products first with two
  children, no top-level product-types item, permission filtering on children.
- **Pest (`tests/core/`)**: the new actions (`CreatesProduct` initial-variant rules,
  `UpdatesProduct` sync surfaces, `DeletesProduct`/`DeletesProductVariant` guards
  incl. model-hook paths and last-variant rule, `GeneratesProductVariants`
  keep/add/remove semantics + guards), `ProductVariant::hasOrderHistory()`, the
  `enabled` flag's `isPurchasable()`/scope behaviour.
- **Pest (`tests/upgrade/`)**: `enabled` backfill.
- **Vitest**: `ProductOptionsBuilder` (add/reorder/remove options, value selection,
  count preview, drift + regenerate lists), `VariantsTable` (filters, bulk bar,
  locked rows), `PricingEditor` (row upserts, minor-unit conversion), `TagsInput`,
  `InventoryCard` (inline adjust), `VariantMediaPicker`, `AvailabilityCard`
  purchasable row.
- `PageScaffoldTest` covers the new pages automatically. PHPStan + Pint as required.

## Alternatives considered

- **A separate `ProductsSection`**: rejected — Brands, Collections and Product Types
  already live in `CatalogSection`; the section is the extension-matching unit, and
  splitting it would fragment `catalog:*` permission handling for no gain.
- **Keep Product Types as a top-level nav item** (just reorder): rejected — the
  prototype is explicit (Products carries an All products / Product types sub-nav),
  the children mechanism already exists in `NavigationItem`, and types are
  schema-for-products, not a peer resource.
- **A product-level SKU column**: rejected — SKUs identify sellable units and belong
  to variants; the list shows the first variant's SKU with a "+N more" badge and the
  simple shape edits the sole variant's SKU inline, which covers everything the
  prototype's product SKU actually did.
- **Persisting the simple/multi shape** (a column, as the prototype's `tweaks.shape`
  implies): rejected — the shape is fully derivable (options attached or variant
  count > 1) and a stored flag could contradict the data.
- **Drafting pricing rows** (per-row draft keys like availability): rejected — base
  rows have currency identity, but group/tier rows have no stable identity until
  saved, so field-level conflict merging degenerates to whole-table conflicts;
  immediate debounced upserts follow the UrlSlugs/media precedent. Stock adjustments
  are likewise immediate — they are movements (an audit ledger), not field edits.
- **Drafting option/variant generation**: rejected — generation restructures rows
  other staff are editing and cannot merge field-wise; the prototype itself treats
  Generate as an explicit committing act with a confirmation dialog.
- **A variant state machine instead of the `enabled` boolean**: rejected — two states
  with no transition rules; the product already owns the lifecycle. Also considered
  mapping "disabled" onto `selling_policy` — rejected, it conflates stock semantics
  with merchandising intent.
- **Requiring a brand before publish** (the prototype's validation): rejected —
  `brand_id` is nullable in core and many catalogues are single-brand; enforcing it
  panel-side would diverge from every other write path.
- **Porting the Reviews column/filter and Import**: rejected — Reviews is add-on
  territory (spec 0049) and the reviews column becomes the worked example of an
  add-on table extension; Import belongs to the future bulk-operations work.
- **A "Schedule publish" date on the Status card**: rejected — no core column;
  channel scheduling (`starts_at`/`ends_at` on the availability card) is the real
  model, and inventing a parallel product-level schedule would shadow it.
- **Uncapped attached options**: rejected — the cartesian product grows fast and
  every comparable admin caps it; 3 matches the prototype and is a validation rule,
  trivially raised later if real demand appears.

## Migration impact

- `product_variants` baseline migration gains `enabled` (boolean, default true,
  indexed) — alpha fold-in; the upgrade package backfills `true`.
- New core contracts, actions and `ProductActionException` are additive. The
  `deleting`-hook guards change failure modes from raw deletes/FK errors to domain
  exceptions on all paths (products with order history; variants with order history
  or last-of-product).
- `ProductVariant::isPurchasable()` starts honouring `enabled` — behaviour change for
  any consumer flipping the new column; default `true` keeps existing data inert.
- Navigation: the `product-types` top-level item moves under `products` — add-ons
  anchored `Position::before/after('product-types')` at the top level fall back to
  priority with a logged warning (the designed `OrderResolver` behaviour).
- Translations: `pricing.php` new group; `products.php`, `availability.php`,
  `nav.php` additions — all 16 locales.
- Filament admin: delete paths on `EditProduct`/variant management delegate to the
  new guarded actions (notifications kept); no visual change. `MapVariantsToProductOptions`
  untouched.
- No new npm dependencies.

## Open questions

None outstanding. Settled during implementation:

- The variant order-history guard lives on the observer (every delete path);
  the last-variant rule lives in `DeletesProductVariant` only, so deleting a
  whole product can still cascade through its final variant.
- The per-location stock table always renders (spec 0038 made locations
  always-on); with one location it is naturally a single row — no module
  toggle.
- `ProductVariant::mappedAttributes()` now returns the type's variant mapping
  (the spec 0056 semantics), and the panel's `AttributeSchema` filters a
  model's mapping by its own morph type.

Resolved during drafting:

- Variant enable/disable ships as a plain `enabled` boolean honoured by
  `isPurchasable()`.
- Pricing, stock, associations, media and generation are immediate sub-resources;
  drafts cover scalar/attribute/relation-id fields only, with the simple shape's
  sole-variant fields riding the product draft as `variant:*` keys.
- The prototype's Reviews/Import/product-SKU/schedule-publish/Stock-Digital-type
  affordances are settled as prototype-isms (see Alternatives).

## References

- Design prototype: `/Users/glenn/GitHub/lunarphp/lunar-v2-ui` —
  `src/pages/{ProductsList,ProductEdit,VariantEdit}.vue`,
  `src/components/{ProductOptions,OptionRow,SharedValuePicker,ExclusiveValueEditor,AddOptionMenu,Variants,VariantsTable,VariantBulkBar,RegenerateDialog,DriftBanner,VariantCountPreview,VariantPricing,VariantInventory,VariantShipping,VariantIdentifiers,VariantTax,VariantAttributes,VariantMedia,VariantShapeToggle,Associations,SideStatus,SideType,SideOrg,SideVariantStatus,SideVariantAxes,Basics,Seo}.vue`,
  `src/composables/useProductVariantBuilder.js`
- [[0049-inertia-panel]] — panel architecture; the SEO-as-slot-example promise
- [[0051-panel-edit-drafts]] — draft autosave/commit/conflict machinery
- [[0052-panel-brands-section]] — shared catalog surfaces; the architectural template
- [[0055-panel-collections-section]] — product picker, availability card
- [[0056-panel-product-types-section]] — attribute mapping; the `default_tax_class_id`
  this spec consumes
- Spec 0038 — inventory fundamentals (stock levels, movements, rollups)
- Spec 0048 — `selling_policy`

## Implementation plan

- [x] Slice 1 — Core: `enabled` column + scope + `isPurchasable()` + upgrade backfill;
      `ProductVariant::hasOrderHistory()`; `CreatesProduct` / `UpdatesProduct` /
      `DeletesProduct` and `UpdatesProductVariant` / `DeletesProductVariant` /
      `GeneratesProductVariants` (+ exception, model-hook guards, Filament
      delegation); core + upgrade tests.
- [x] Slice 2 — Panel scaffold + list: navigation restructure (products parent +
      children, product-types moved), routes, `ProductIndexController` (KPIs,
      filters), `ProductsTableExtension`, bulk status, create flow, Index/Create
      pages, lang keys (16 locales), feature + nav tests. (Landed together with
      slice 3 — the list's row links and create redirect anchor on the edit page,
      the collections precedent.)
- [x] Slice 3 — Edit page core: edit controller, `ProductDraftResource`, Edit page
      with Basics / `MediaManager` / `AttributeFields` / `UrlSlugs` / Associations,
      sidebar (Status, `AvailabilityCard` + purchasable, Product type, Organization
      with `TagsInput`, Activity), slot zones, `DuplicateProductPageAction`,
      media/URL endpoints, tests.
- [x] Slice 4 — Pricing + simple shape: price endpoints + `PricingEditor`,
      `InventoryCard` + stock-adjust endpoint, Shipping/Identifiers/Tax cards, the
      sole-variant `variant:*` draft surface and shape toggle, `pricing.php`
      (16 locales), tests. (Also landed here: nested variant routes bind as
      `{productVariant}` so the core model binder scopes children in segment
      order, with `Product` mapping the guessed relation name, and the shared
      `EditDraftController` targeting the deepest bound model.)
- [x] Slice 5 — Options + variants: `ProductOptionsBuilder` component family,
      product-options search endpoint, `options.generate` flow (drift, regenerate
      dialog, collapse), `VariantsTable` + bulk endpoints, vitest + feature tests.
      (Landed together with slice 6 — the table rows anchor on the variant edit
      page.)
- [x] Slice 6 — Variant edit page: controller + `ProductVariantDraftResource`,
      VariantEdit page (pricing, `VariantMediaPicker`, inventory, cards, sidebar,
      prev/next), variant media/stock endpoints, tests.
- [x] Slice 7 — Extension example: SEO card in `packages/panel-addon-example`
      injected into `products.edit:content:after`, `TMP-DOCS.md` slot walkthrough.
