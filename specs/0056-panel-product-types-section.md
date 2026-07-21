# 0056 — Panel Product Types section

- Status: implemented
- Author: Glenn Jacobs
- Created: 2026-07-20
- TODO item: Panel Product Types section — attribute mapping, type-level content, media, tax defaults (spec 0056)

## Problem

The panel's Catalog side ships Brands (spec 0052) and Collections (spec 0055). Product
types are the remaining catalog resource before the Products build, and the one Products
depends on most: a product type defines which attributes its products and variants
carry, so the Products edit screen cannot render its attribute fields until staff can
manage the mapping. The design prototype (`lunar-v2-ui`: `ProductTypesList.vue`,
`ProductTypeEdit.vue`) defines the screens; their defining feature — two attribute
pickers mapping product and variant attributes — has no panel component yet.

Core support is thin. `ProductType` carries `id`, `public_id`, `name` and the
`product_type_attribute` pivot; the Filament resource offers a name field and two
`AttributeSelector` tabs. Several prototype capabilities have no core backing, and one
naming collision needs settling:

- The prototype gives types an **active/draft status** (list filter, bulk actions,
  sidebar segmented control) with the copy "available in / hidden from the product
  create flow". `product_types` has no status column. The semantics need pinning down:
  status must **not** cascade to existing products — product visibility already has its
  own state machine (spec 0021), and a type flipped to draft silently unpublishing its
  whole catalogue would be a footgun.
- The prototype shows a **handle**; types have none, unlike brands, collections,
  channels, customer groups and locations.
- The prototype has a **description** textarea ("internal note for your team and, if
  you wish, content for a headless storefront page"). No column.
- The prototype's sidebar has a **Defaults card with a default tax class**, pre-filled
  on new products of the type. No column, and no consumer until the Products build.
- The prototype gives the type **media** and **"About this type" attribute values** —
  content for a type landing page. `ProductType` is neither a media model nor
  attributable, and its `mappedAttributes()` already means something else: the pivot of
  attributes mapped onto its *products and variants*, not the
  `HasAttributeData::mappedAttributes()` meaning ("the attributes applicable to this
  model's morph type") every other attributable model shares.
- There are **no core actions** for product type writes. Deletion is guarded only in
  Filament's `EditProductType::before()`; the `products.product_type_id` FK is
  RESTRICT, so every unguarded path — `ProductType::delete()`, the Filament table's
  `DeleteBulkAction`, consumer code — throws a raw `QueryException`.

Two prototype-isms do not survive contact with core and are settled here rather than
ported: the list's tri-count Attributes column (type · product · variant) assumes
per-type selection of type-level fields, but core scopes attributes globally by morph
type, so the type-level count would be identical on every row; and the prototype has no
create route at all (the "New product type" button is a stub).

## Proposal

Product Types list/create/edit screens in the existing `CatalogSection`, matching the
design prototype and following the Brands architecture (core action delegation, edit
drafts, table-extension row/bulk actions, immediate media sub-resource). One new shared
surface: an attribute picker component, built for reuse by any later
attribute-assignment UI.

### Core additions

**Status.** `status` string column on the `product_types` baseline migration (alpha
fold-in), default `active`, indexed, cast to a new `States/ProductType/ProductTypeState`
state machine with `Draft` and `Active` states — the `BrandState` shape.
`ProductType::scopeActive()` mirrors `Brand::scopeActive()`. Semantics: status gates the
**product create flow only** — the future Products section's type selector lists active
types, and a draft type cannot be chosen for new products. Existing products are
untouched; their visibility is governed by their own state machine (spec 0021). Nothing
in storefront query paths reads type status. Default `active` for the same reason brands
default active: types are schema metadata, not gated content, and v1 upgraders keep
every type usable — the upgrade package backfills `active`.

**Handle.** `handle` unique string column on the baseline, kebab-case via
`Str::slug($name)`, auto-generated with numeric-suffix uniqueness by the model
`creating` hook, with a `replicating` hook clearing it — the brands/collections
convention, lifted verbatim. `ProductTypeFactory` derives it from the name; the upgrade
package backfills v1 types from their names.

**Description.** Nullable `description` text column, plain (not translated): the name is
plain, and the type's storefront-facing content now lives in type-level attributes,
which translate per field type. The description is the internal note the prototype's
help text describes first.

**Default tax class.** Nullable `default_tax_class_id` FK on the baseline,
`nullOnDelete`. No behaviour ships with it here — the Products build pre-fills the tax
class on new products of the type. Storing it now keeps the edit screen faithful to the
prototype and saves a second alpha fold-in later.

**Type-level attributes.** `ProductType` becomes attributable, which first requires
freeing the `mappedAttributes` name:

- Rename the pivot relation `ProductType::mappedAttributes()` →
  `ProductType::attributeMapping()`. The pivot ("which attributes this type maps onto
  its products and variants") and the `HasAttributeData` meaning ("this model's own
  fields") are different concepts that happen to share a name; the attributable trait's
  meaning wins because every other attributable model already uses it.
  `productAttributes()` and `variantAttributes()` stay, rebuilt on `attributeMapping()`.
  Call sites updated: `Product::mappedAttributes()` (delegates to the pivot), Filament's
  `ProductTypeForm` (`relationship('attributeMapping')` on both selector tabs). Public
  contract break → Rector rule in the `upgrade` package rewriting `mappedAttributes`
  method calls and property fetches on `ProductType`-typed expressions.
- Add `HasAttributeData` to the model (the class no longer defines `mappedAttributes()`,
  so the trait's version — attributes for the `product_type` morph — applies) plus an
  `attribute_data` column on the baseline.
- Register `ProductType::class` in `AttributeManifest::$baseTypes` so Settings →
  Attributes can create attributes targeting product types.

Type-level fields are scoped globally by morph type, like every attributable model: all
product types share the same field set, each with its own values. The prototype's
per-type `typeAttributeIds` selection is not ported (see Alternatives).

**Media.** The `HasMedia` concern + Spatie interface on the model, the brands shape, and
`'product_type' => StandardDefinitions::class` in `config/media.php` definitions. The
panel reuses the spec 0052 media actions and `MediaManager` wholesale.

**Actions.** New contracts in `Contracts/Actions/ProductTypes/` with implementations in
`Actions/ProductTypes/`, registered in `ActionServiceProvider`:

- `CreatesProductType` — creates from validated attributes (name, handle, status,
  description, `default_tax_class_id`), handle generated by the model hook when absent.
- `UpdatesProductType` — updates fields and attribute data, and syncs
  `attributeMapping()` from the union of validated product and variant attribute id
  sets.
- `DeletesProductType` — refuses (new `ProductTypeActionException`) while
  `products()->exists()`. The guard also lives on the model's `deleting` hook (the
  spec 0052 hardening), so every delete path throws the domain exception instead of a
  FK `QueryException`.

Filament impact: `EditProductType::before()` delegates its existing guard to
`DeletesProductType` (notification kept); the table's `DeleteBulkAction` — today an
unguarded FK violation waiting to happen — gains protected-row handling, notifying and
skipping types that still have products.

### Panel server side

**`CatalogSection`** grows a third resource, mirroring Brands:

- Nav item `product-types` after Collections, `boxes`-style icon, permission
  `catalog:manage-products` (the handle the Filament `ProductTypeResource` already
  uses; no new permission).
- `routes()` — prefix `product-types`, names `panel.product-types.*`, middleware
  `can:catalog:manage-products`: `index`, `create`, `store`,
  `bulk-status` (`POST /bulk/status/{status}` whereIn active/draft, the brands shape),
  `edit`, `update`, `destroy`, the three standard `draft.*` routes on
  `EditDraftController`, and nested `media.store|update|destroy|reorder` under
  `Route::scopeBindings()`. No `urls.*` — types are not storefront-routable, the one
  brands surface that does not carry over.
- `tableExtensions()` — `['product-types.index' => ProductTypesTableExtension::class]`.
- `draftables()` — `[ProductTypeDraftResource::class]`.

**Controllers** (`Http/Controllers/ProductTypes/`), writes delegated to the core
actions:

- `ProductTypeIndexController` — columns: name (with mono handle sub-line),
  description (truncated), attributes (product count · variant count, via
  `withCount(['productAttributes', 'variantAttributes'])`), products count, status.
  Search matches name and handle. Filters: status. Sort allow-list: name, created_at,
  products_count (default `created_at` desc). `paginate(15)`, rows shaped with
  `edit_url` and `_actions` via the table-extension resolver. No KPI strip (the
  prototype has none).
- `ProductTypeCreateController` — minimal create (name + status), redirect to edit with
  flash — the brands convention; the prototype has no create flow to port.
- `ProductTypeEditController` — renders `product-types/Edit` with: type fields, `draft`,
  attribute schema + values (via `AttributeSchema`, which now works unmodified because
  `mappedAttributes()` carries the trait meaning), `media`, the two picker payloads
  (all attributes for the `Product` and `ProductVariant` morphs, grouped by
  `AttributeGroup`, each attribute with handle, name, field-type token and required
  flag), the currently mapped id sets, products count, recent activity (`LogsActivity`
  is already on the model), and `bulk-status`/`destroy` handling as brands.
- `ProductTypeBulkStatusController`, `ProductTypeMediaController` — the brands shapes;
  media endpoints call the spec 0052 media actions, immediate rather than drafted.

**Requests** (`Http/Requests/ProductTypes/`): `ProductTypeRequest` — name required max
255; handle nullable slug-format unique; status in-state-list; description nullable
string; `default_tax_class_id` nullable exists; attribute values validated against the
schema; `product_attribute_ids` / `variant_attribute_ids` arrays whose ids must exist
and belong to the matching morph's attribute set (a product attribute id posted in the
variant list is rejected, not silently re-homed).

**`ProductTypeDraftResource`** — draftable fields: `name`, `handle`, `status`,
`description`, `default_tax_class_id`, per-attribute keys (`attribute:{handle}`), and
normalised sorted `product_attribute_ids` / `variant_attribute_ids` so two staff
mapping different surfaces never conflict. `rules()` delegates to `ProductTypeRequest`;
`commit()` to `UpdatesProductType`. Media is an immediate sub-resource outside the
draft.

**`ProductTypesTableExtension`** — `EditProductTypeAction`, `DeleteProductTypeAction`
(confirmation message; URL `null` while `products_count > 0`, the ProductOptions
precedent, with the server guard as backstop), and `SetProductTypesActiveBulkAction` /
`SetProductTypesDraftBulkAction`.

### Shared surface — attribute picker

`AttributePicker.vue`, the prototype's `AttributeSelector.vue`: collapsible
`AttributeGroup` sections (header shows the group name and a selected/total count, with
an All/None toggle), a search box over name and handle, per-attribute rows of checkbox +
name + mono handle + field-type pill + Required pill, and an "X of Y selected" footer
count with a "Manage attributes in Settings" link. Purely presentational: it receives
the grouped payload as a prop and emits the selected id array, which lives on the page's
draft form — autosave, dirty tracking and conflict handling come free from
`useEditDraft`, exactly like `AttributeFields` values.

Not exported on `ui.ts` yet — same policy as the spec 0052 surfaces: it stabilises
through the Products build first.

### Pages

`pages/product-types/{Index,Create,Edit}.vue`, structured like the brands pages
(`PanelLayout` + `Breadcrumbs` + `PageHeader` + `PageZone`, enforced by
`PageScaffoldTest`):

- **Index** — toolbar (debounced search, status `FilterDropdown`, sort dropdown, result
  count), `BulkActionsToolbar` (set active / set draft), `DataTable`, `Pagination`,
  `PageEmpty`. Primary header action: "New product type".
- **Create** — name + status segmented control; posts to `store`, redirects to edit.
- **Edit** — two-column. Main: Basics (name with auto-synced handle until manually
  edited, description textarea), `MediaManager`, `AttributeFields` ("About this type"),
  and two `AttributePicker`s — Product attributes, Variant attributes. Sidebar
  `SideCard`s: Status (`StatusSegmentedControl`, drafted, copy "Available in / Hidden
  from the product create flow"), Usage (products count — plain count until a Products
  section exists to link to), Defaults (tax class `Select` over all tax classes, hint
  "pre-filled on new products"), Activity (`ActivityTimeline`). Save cluster is
  `DraftActions` + `DraftConflictDialog` via `useEditDraft`.

The prototype's "Duplicate" page action, kebab row menu and AI description generation
are out of scope, as they were for Brands.

### Translations

New lang group `product-types.php` plus a `nav.php` key — English first, mirrored
across all 16 locales.

### Testing

- **Pest (`tests/panel/Feature/ProductTypes/`)**: index (render, search by name/handle,
  status filter, sort allow-list + fallback, pagination, row shaping incl. attribute
  counts), create, edit props (schema shape, picker payloads, mapped id sets, media),
  draft lifecycle for a scalar field, an `attribute:{handle}` field and the id-set
  fields (autosave, commit, conflict), mapping sync through update (incl. cross-morph
  id rejection), media endpoints, destroy guard (type with products → error flash,
  nothing deleted), bulk status, permission gating.
- **Pest (`tests/core/`)**: the new actions (create with/without handle, update mapping
  sync, delete guard incl. the model-hook path), `ProductTypeState` transitions,
  `scopeActive`, manifest registration, handle generation/replication hooks.
- **Pest (`tests/filament/` / `tests/admin/`)**: the renamed relationship still drives
  both selector tabs; bulk delete skips protected types.
- **Pest (`tests/upgrade/`)**: Rector rule rewrites `mappedAttributes` on
  `ProductType`-typed calls; backfills covered by the upgrade suite conventions.
- **Vitest**: `AttributePicker` component tests (group toggle, search, emit shape).
- `PageScaffoldTest` covers the new pages automatically. PHPStan + Pint as required.

## Alternatives considered

- **Cascade type status to products** (draft type hides its products): rejected — one
  toggle silently unpublishing a whole catalogue is a footgun, products already carry
  their own published state (spec 0021), and every storefront product query would grow
  a join through `product_types`. Create-flow gating is what the prototype's own copy
  describes.
- **Ship without status**: considered (types are schema metadata), but the list filter,
  bulk actions and sidebar control all hang off it and the brands state-machine shape
  makes the column cheap. Included.
- **Place under Settings** (the ProductOptions shape): rejected — the prototype's
  breadcrumb is Catalog / Product types, the Filament resource lives in the Catalog
  group under the same permission, and the edit screen is a full content surface
  (media, attribute values, drafts), not a settings dialog.
- **Keep the pivot named `mappedAttributes` and special-case `AttributeSchema`**:
  rejected — every attributable model reads `mappedAttributes()` as "my own fields";
  a ProductType special case would leak into the panel serializer and every future
  consumer. One Rector-covered rename beats a permanent semantic fork.
- **Per-type selection of type-level fields** (the prototype's `typeAttributeIds`):
  rejected — core scopes attributes globally by morph type; a second pivot for
  type-self fields adds schema and UI for no known consumer. The "About this type"
  panel renders the global `product_type` attribute set.
- **Translated description**: rejected — the name is plain, and translated
  storefront-facing content belongs in type-level attributes where field types already
  handle locales.
- **A `catalog:manage-product-types` permission**: rejected — Filament gates the
  resource behind `catalog:manage-products`; the panel matches it so one staff role
  behaves identically in both admins.

## Migration impact

- `product_types` baseline migration gains `status` (default `active`), `handle`
  (unique), `description` (nullable), `default_tax_class_id` (nullable FK,
  `nullOnDelete`) and `attribute_data` — alpha fold-ins. The upgrade package backfills
  `active` and name-derived handles for v1 migrators.
- `ProductType::mappedAttributes()` → `attributeMapping()` is a public contract break:
  Rector rule in the `upgrade` package; `productAttributes()` / `variantAttributes()`
  unchanged. `HasAttributeData` then gives `mappedAttributes()` /
  `->mapped_attributes`-style access the standard attributable meaning.
- New core contracts, actions and `ProductTypeActionException` are additive. The
  model-level delete guard changes failure mode from FK `QueryException` to the domain
  exception on all paths.
- `AttributeManifest` gains the `producttype` type; Settings → Attributes surfaces it
  automatically.
- `config/media.php` gains the `product_type` definition entry.
- Filament admin: form relationship rename, `EditProductType` guard delegation, table
  bulk-delete protected handling. No visual change.
- Translations: one new panel lang group plus nav keys, all 16 locales.

## Open questions

None outstanding. Resolved during drafting:

- Status is in, with create-flow-only semantics (no cascade to existing products).
- Description is plain text; translated content lives in type-level attributes.
- The list's Attributes column shows product · variant counts (the prototype's
  type-level count would be constant across rows under global morph scoping).

## References

- Design prototype: `/Users/glenn/GitHub/lunarphp/lunar-v2-ui` —
  `src/pages/ProductTypesList.vue`, `src/pages/ProductTypeEdit.vue`,
  `src/components/AttributeSelector.vue`
- [[0049-inertia-panel]] — panel architecture
- [[0051-panel-edit-drafts]] — draft autosave/commit/conflict machinery
- [[0052-panel-brands-section]] — the architecture this section follows; media and
  attribute-field surfaces reused here
- [[0055-panel-collections-section]] — handle-generation convention
- Spec 0021 — state machines (the `ProductTypeState` shape)

## Implementation plan

- [x] Slice 1 — Core: baseline columns (`status`, `handle`, `description`,
      `default_tax_class_id`, `attribute_data`); `ProductTypeState` + `scopeActive`;
      handle hooks + factory + upgrade backfills; pivot rename + Rector rule +
      call-site updates; `HasAttributeData` + manifest registration; media concern +
      definition; `CreatesProductType` / `UpdatesProductType` / `DeletesProductType`
      (+ exception, model-hook guard, Filament delegation and bulk-delete handling);
      core/filament/upgrade tests.
- [x] Slice 2 — Panel scaffold: `CatalogSection` nav + routes + extension registration,
      index/create/bulk-status controllers, `ProductTypeRequest`,
      `ProductTypesTableExtension` + actions, Index and Create pages,
      `product-types.php` + nav lang keys (16 locales), feature tests.
- [x] Slice 3 — Edit surface: edit controller + media endpoints,
      `ProductTypeDraftResource`, Edit page (Basics, `MediaManager`,
      `AttributeFields`, sidebar cards), `AttributePicker` component + vitest,
      draft/mapping/media feature tests.
