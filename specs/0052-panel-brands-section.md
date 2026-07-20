# 0052 — Panel Brands section and shared catalog editing surfaces

- Status: implemented
- Author: Glenn Jacobs
- Created: 2026-07-17
- TODO item: Panel Brands section + shared catalog editing surfaces (spec 0052)

## Problem

The panel ships Customers CRUD and Channels settings (spec 0049) and nothing from the
Catalog side. Brands is the natural first catalog resource: small surface, but its edit
screen exercises every content-editing capability the design prototype gives Products and
Collections too — media management, custom attribute fields, per-locale URL slugs, and
collection membership. None of those exist in the panel today (the component inventory
has no media manager, no attribute renderer, no slug editor, no relation picker), and
spec 0049 explicitly deferred dynamic attribute/field-type rendering.

Building Brands without designing those pieces for reuse would mean rebuilding them for
Products and Collections. Building them Brand-first, as shared components with their own
lang groups and server seams, means the later (much larger) Products work composes
existing parts.

Three places where the design prototype (`lunar-v2-ui`: `BrandsList.vue`, `BrandEdit.vue`)
and the core model disagree also need settling:

- The prototype gives brands an **active/draft status** (list column, filter, bulk
  actions, sidebar segmented control). The `brands` table has no status column —
  products have one, backed by the spec 0021 state machine (`States/Product`).
- The prototype shows a **handle** field. Brands have no handle column, unlike channels,
  customer groups, collection groups and locations, which all carry one as their stable
  programmatic key.
- The prototype has a single **description** textarea ("short brand summary — shown on
  listing rows"). Core has translated `short_description` and rich-text `description`.

## Proposal

A new `CatalogSection` in `lunarphp/panel` with Brands list/create/edit screens matching
the design prototype, following the Customers architecture exactly (section class, core
action delegation, table extensions, edit drafts). Alongside it, four shared editing
surfaces built as reusable panel components with their own lang groups — media manager,
attribute fields, URL slugs, collection picker — plus shared translated-input and
rich-text primitives.

### Core additions

**Brand status.** `status` string column on the `brands` baseline migration (folded in
per the alpha rule), default `active`, indexed, cast to a new `States/Brand/BrandState`
state machine with `Draft` and `Active` states — the same shape as `States/Product`.
`Brand::scopeActive()` mirrors `Product::scopePublished()`. Default is `active` (not
`draft` as products default) — brands are curation metadata rather than gated content,
so existing storefronts and v1 upgrades keep every brand visible; the upgrade package
backfills `active`, and the panel's create form also defaults to active.

**Brand handle.** `handle` unique string column on the `brands` baseline migration.
Channels, customer groups, collection groups and locations all carry a unique handle as
the stable programmatic key, and brands need one for the same reason: storefront code
and seeders referencing a brand (featured-brand blocks, brand landing routes) can't use
the URL slug (per-language, staff-editable) or `public_id` (opaque). Kebab-case via
`Str::slug($name)` (the dominant convention across the existing handle forms and
factories), auto-generated with numeric-suffix uniqueness in `CreatesBrand` when not
supplied. `BrandFactory` derives it from the name; the upgrade package backfills v1
brands from their names.

**Brand actions.** Brand writes currently happen as plain Eloquent (Filament) — the panel
needs the customer-style seams. New contracts in `Contracts/Actions/Brands/` with
implementations in `Actions/Brands/`, registered in `ActionServiceProvider`:

- `CreatesBrand` — creates from validated attributes (name, handle, status, translated
  descriptions), generating a unique handle from the name when absent; `HasUrls`
  generates the default slug as today.
- `UpdatesBrand` — updates fields, attribute data, and syncs `collections()`.
- `DeletesBrand` — refuses (domain exception) when the brand has products. This moves the
  guard that today lives only in Filament's `EditBrand::before()` into the seam; the
  Filament page keeps its notification but delegates the check.

**URL actions.** Slug management is generic — any `HasUrls` element. New contracts in
`Contracts/Actions/Urls/`: `CreatesUrl`, `UpdatesUrl`, `DeletesUrl`. Each takes the
element plus validated data (`language_id`, `slug`, `default`), enforces one default per
element, and re-points the default on delete. Products and Collections panel work reuses
these; the Filament URL pages can adopt them later without behaviour change.

**Media actions.** Same reasoning: `Contracts/Actions/Media/` — `AddsMedia` (file +
collection + custom properties), `UpdatesMedia`, `DeletesMedia`, `ReordersMedia`
(ordered id list via spatie's `order_column`; first becomes primary). Thin wrappers over
spatie/laravel-medialibrary so panel controllers stay action-driven. Media metadata
lives in `custom_properties`, which today holds only `name` and `primary` (the latter
normalised to a single primary per collection by the existing `MediaObserver` — the
actions write the flag and rely on the observer). `UpdatesMedia` manages three new keys:

- `alt` — string, image alt text.
- `caption` — string, optional display caption.
- `focal` — `{x, y}` integer percentages (0–100), the focal point. Matching
  spatie/image's `focalCrop($width, $height, $focalX, $focalY)` percent convention.

The focal point's canonical home is the custom property: display surfaces (panel tiles,
storefronts) apply it as CSS `object-position: {x}% {y}%` on `object-fit: cover`
images, which is how the design prototype renders it. Generated conversion files are
unaffected by default — every `StandardDefinitions` conversion uses `Fit::Fill`
(letterboxed, never cropped). Where cropping conversions *do* exist (a host app's custom
media definitions can register `Fit::Crop` conversions), `UpdatesMedia` mirrors the
focal point into the media item's per-media `manipulations`
(`['{conversion}' => ['focalCrop' => [w, h, x, y]]]`) for each cropping conversion;
saving the media triggers spatie's automatic regeneration of that item's conversions
(queued, per the spatie default). With the standard definitions this mirroring is a
no-op, so no regeneration churn is introduced.

### Panel server side

**`Sections/Catalog/CatalogSection.php`** — key `catalog`, nav group "Catalog" with a
Brands item (tag icon), permission `catalog:manage-products` (the handle the Filament
`BrandResource` already uses; no new permission). Registers:

- `routes()` — prefix `brands`, names `panel.brands.*`, middleware
  `can:catalog:manage-products`: `index`, `create`, `store`, `edit`, `update`, `destroy`,
  the three standard `draft.*` routes on `EditDraftController`, nested
  `media.store|update|destroy|reorder` and `urls.store|update|destroy`, and
  `collections.search` (lightweight id/name/breadcrumb JSON for the picker, reusable by
  later catalog screens).
- `tableExtensions()` — `['brands.index' => BrandsTableExtension::class]`.
- `draftables()` — `[BrandDraftResource::class]`.

**Controllers** (`Http/Controllers/Brands/`), delegating writes to the core actions:

- `BrandIndexController` — columns: name (thumbnail `small` conversion + handle mono
  sub-line), short description, collections count, products count, status. Search
  matches name, handle and URL slugs. Filters: status. Sort allow-list: name, created_at,
  products_count (default `created_at` desc). `paginate(15)`, rows shaped with
  `edit_url` and `_actions` via the table-extension resolver, exactly as customers. No
  KPI strip (the prototype has none — just the "N of M" count the toolbar shows).
- `BrandCreateController` — minimal create (name + status), redirect to edit with flash.
- `BrandEditController` — renders `brands/Edit` with: brand fields, `draft`,
  `attributeSchema` + values, `media` (id, urls, name, alt, primary, position),
  `urls` + `languages`, selected collections (id/name/breadcrumb), products count,
  recent activity (the `LogsActivity` log, as customers), and the `urls` route map.
- `BrandMediaController`, `BrandUrlController` — sub-resource endpoints calling the
  media/URL actions; immediate persistence, not drafted (the customer-addresses model).

**Requests** (`Http/Requests/Brands/`): `BrandRequest` (name required max 255, handle
nullable slug-format unique, status in-state-list, translated `short_description` and
`description` maps, attribute values validated against the schema, `collection_ids`
array of existing ids), `BrandUrlRequest` (slug required, slugified, unique per
`element_type` + `language_id` — the Filament rules), `BrandMediaRequest`.

**`BrandDraftResource`** — draftable fields: `name`, `handle`, `status`,
`short_description`, `description`, per-attribute keys (`attribute.{handle}`, so two
staff editing different attributes never conflict), `collection_ids` (normalised sorted
ids). `rules()` delegates to
`BrandRequest`; `commit()` to `UpdatesBrand`. Media and URLs are immediate
sub-resources outside the draft.

**`BrandsTableExtension`** — `EditBrandAction`, `DeleteBrandAction` (confirmation
message; server surfaces the products-guard failure as a flash error). Bulk actions per
the prototype: `SetBrandsActiveBulkAction`, `SetBrandsDraftBulkAction`.

### Shared surface 1 — attribute fields

The piece spec 0049 deferred, scoped to what Brands needs but built generically.

- **Schema serializer** (`Support/AttributeSchema`): builds an `attributeSchema` prop
  from `$model->mappedAttributes()` grouped by `AttributeGroup` (both position-ordered):
  per attribute — handle, translated label, required flag, and a field-type token with
  its config (`text` (+richtext flag), `translated-text`, `dropdown` (+options),
  `number`, `toggle`, `list`, `youtube`, `vimeo`, `file`). `youtube`/`vimeo` store a
  bare video-ID string; the panel renders them as the Filament admin does — an ID text
  input with a debounced live `<iframe>` embed preview when non-empty. `file` (a plain
  disk upload configured per attribute, not media-library-bound) renders its stored
  path(s) read-only in v1; a config-driven upload endpoint is a follow-up.
- **Rules builder**: request rules derived from the same schema (required, numeric,
  in-options, …) so validation is server-owned; `BrandRequest` composes them.
- **`AttributeFields.vue`**: the prototype's `Attributes.vue` — collapsible group
  sections with expand/collapse-all, three-column field grid, token → input mapping onto
  existing primitives (`TextInput`, `Select`, `Toggle`, `Textarea`, `FieldLabel`).
  Unknown tokens render a read-only fallback naming the type. Values are plain
  `attribute.{handle}` entries on the page's draft form, so autosave, dirty tracking and
  conflict resolution come free from `useEditDraft`.
- Strings live in a new shared `attributes.php` lang group.

Customers can adopt the same component later (customers are attributable too); not in
this spec's scope.

### Shared surface 2 — media manager

`MediaManager.vue`, the prototype's `Media.vue`: upload (multipart, with the drop-zone
empty state), tile grid where the first tile is the hero/primary (spanning 2x2), drag
reorder persisting via the reorder endpoint, per-tile edit dialog, delete. Props: items
+ endpoint URLs; owns its own fetch/reload via partial Inertia reloads. Tiles use the
`small` conversion and apply the focal point as `object-position`; the `images`
collection via the model's media definitions.

The edit dialog follows the prototype's `MediaEditDialog.vue`: a `FocalPointEditor`
(click/drag crosshair over the image) on the left; name, alt text (required), caption,
and a `CropPreview` strip (1:1, 4:5, 16:9, 9:16, simulated client-side with
`object-position` — no server round-trip) on the right. Saving posts to the media
update endpoint, which writes the custom properties and focal mirroring through
`UpdatesMedia` (see Core additions). The dialog's AI-generate buttons are out of scope.
Strings in a shared `media.php` lang group.

### Shared surface 3 — URL slugs

`UrlSlugs.vue`, the prototype's `Slugs.vue`: table of one row per URL — language,
prefixed slug input, default badge-toggle, delete — plus "add language" for languages
without a row. Rows save per-row through the nested URL endpoints on change (debounced),
with inline validation errors (slug format, per-language uniqueness). One default is
always enforced server-side. The prototype's storefront preview link is rendered only
when the host app configures a base URL (`lunar.panel.storefront_url`, new config key,
null default). Strings in a shared `urls.php` lang group.

The handle field in Basics and the URL slugs here are distinct on purpose: the handle is
the stable programmatic key, the slugs are per-language storefront routing.

### Shared surface 4 — collection picker

`CollectionPicker.vue` + `CollectionPickerDialog.vue`, per the prototype: chip list with
remove buttons and an "add" affordance opening a search dialog backed by the
`collections.search` endpoint (name + breadcrumb rows, multi-select, existing ids
disabled). Selection is a `collection_ids` array on the draft form; `UpdatesBrand` syncs
the pivot on commit. Strings in the shared `common.php`/`collections`-adjacent keys —
a small `collections.php` group.

None of the four components are exported on `ui.ts` yet — they stabilise through the
Products build first, then get promoted to the add-on surface deliberately.

### Pages

`pages/brands/{Index,Create,Edit}.vue`, structured like the customers pages
(`PanelLayout` + `Breadcrumbs` + `PageHeader` + `PageZone` before/after, enforced by
`PageScaffoldTest`):

- **Index** — toolbar (debounced search, status `FilterDropdown`, sort dropdown, result
  count), `BulkActionsToolbar` (set active / set draft), `DataTable` with the columns
  above, `Pagination`, `PageEmpty`. Primary header action: "New brand".
- **Create** — name + status segmented control; posts to `store`, redirects to edit.
- **Edit** — two-column: main column Basics, `MediaManager`, `AttributeFields`,
  `UrlSlugs`; sidebar `SideCard`s — Status (new `StatusSegmentedControl` component,
  drafted field), Usage (products count; plain count until a Products section exists to
  link to), Collections (`CollectionPicker`), Activity (recent activity-log entries via
  `ActivityTimeline`). Save cluster is `DraftActions` + `DraftConflictDialog` via
  `useEditDraft`, as customers.

Basics mirrors the prototype's Product `Basics.vue` for consistency across catalog
screens: a name + handle row (handle mono, auto-synced from the name until manually
edited — the prototype's brand-edit behaviour), a single-line **short description**
input with the character-count hint, and a **description** rich-text editor. Two small
shared primitives support this:

- `TranslatedInput` — locale switcher (languages from core) wrapping a text input,
  textarea or the rich-text editor, for the translated description maps. A
  design-prototype gap, kept minimal.
- `RichTextEditor` — TipTap-based, reading and writing HTML so the same
  `description` value round-trips with Filament's rich-text component. Toolbar per the
  prototype: bold/italic/underline, H1/H2, link. The prototype's image and "Rewrite
  with AI" toolbar buttons are out of scope. Adds the TipTap packages as panel-build
  dependencies (`@tiptap/vue-3`, `@tiptap/starter-kit`).

The prototype's "Duplicate" page action and AI description generation are out of scope.

### Translations

New lang groups `brands.php`, `attributes.php`, `media.php`, `urls.php`,
`collections.php` plus `nav.php` additions (`catalog`, `brands`) — English first,
mirrored across all 16 locales, landing with the slice that introduces each group.

### Testing

- **Pest (`tests/panel/Feature/Brands/`)**: index (render, search by name/slug, status
  filter, sort allow-list + fallback, pagination, row shaping), create, edit props
  (attribute schema shape, media/urls/collections payloads), draft lifecycle for a
  scalar field, an `attribute.{handle}` field and `collection_ids` (autosave, commit,
  409 conflict, rebase), media endpoints (upload, name/alt/caption/focal update — focal
  persisted as 0-100 percentages, `manipulations` mirroring only when a cropping
  conversion is registered, single-primary invariant held by `MediaObserver` on reorder,
  delete), URL endpoints (create, per-language uniqueness, default re-pointing,
  delete), destroy guard (brand with products → error flash, nothing deleted), bulk
  status actions, permission gating.
- **Pest (`tests/core/`)**: the new actions (including `DeletesBrand` guard and URL
  default handling), `BrandState` transitions, `scopeActive`.
- **Unit**: `AttributeSchema` serializer and rules builder.
- **Vitest**: `AttributeFields`, `UrlSlugs`, `MediaManager`, `FocalPointEditor`,
  `CollectionPicker`, `StatusSegmentedControl`, `TranslatedInput`, `RichTextEditor`
  component tests.
- `PageScaffoldTest` covers the new pages automatically. PHPStan + Pint as required.

## Alternatives considered

- **Ship Brands without status** (defer the core change): avoids touching core, but the
  prototype's list filter, bulk actions and sidebar control all hang off status, and
  Products already set the state-machine precedent. Rejected — the column is small,
  additive, and `active`-defaulted so nothing breaks.
- **Treat the default URL slug as the handle** (no new column): rejected — slugs are
  per-language and staff-editable, so code referencing a brand by slug breaks on rename
  or localisation; every comparable core model (channel, customer group, collection
  group, location) already separates handle from display/routing values.
- **Hand-rolled `contenteditable` editor instead of TipTap**: rejected — link/heading
  handling and HTML sanitisation are exactly the problems editor libraries solve; TipTap
  is Vue-3-native, tree-shakeable, and emits the same HTML Filament's editor stores.
- **Reuse Filament's `Attributes` component knowledge via a JSON bridge**: the Filament
  component is Livewire-bound; only the underlying `AttributeData`/field-type model is
  reusable, which the schema serializer consumes directly.
- **Draft media and URLs too**: rejected — they are sub-resources with their own
  endpoints (the customer-addresses pattern); drafting binary uploads and slug
  uniqueness checks buys conflict complexity for no real concurrent-edit win.
- **A new `catalog:manage-brands` permission**: rejected for now — Filament's
  `BrandResource` gates brands behind `catalog:manage-products`; the panel matches it so
  one staff role behaves identically in both admins.
- **Export the new components on `ui.ts` immediately**: rejected — the add-on surface is
  a compatibility promise; promote after the Products build proves the APIs.

## Migration impact

- `brands` baseline migration gains `status` (default `active`) and `handle` (unique) —
  alpha fold-ins; the upgrade package backfills `active` and name-derived handles for
  v1 migrators.
- New core contracts and actions are additive. `DeletesBrand` centralises the
  products-guard that Filament already enforces. Hardened during review: the guard also
  lives on the model's `deleting` hook, so every delete path — `Brand::delete()`, bulk
  actions, consumer code — throws `BrandActionException` while products reference the
  brand (previously a direct delete orphaned them by nulling `brand_id`).
- New config key `lunar.panel.storefront_url` (null default).
- New panel-build npm dependencies: `@tiptap/vue-3`, `@tiptap/starter-kit` (private
  `@lunarphp/panel-build` package only; nothing added to the published add-on packages).
- Translations: five new panel lang groups plus nav keys, all 16 locales.
- Filament admin: `EditBrand` delete guard delegates to `DeletesBrand`; no UI change.
  Its media form (name + primary only) is untouched — `alt`/`caption`/`focal` are
  additive `custom_properties` keys it can adopt later.

## Open questions

None outstanding. Resolved during review:

- Brand status is in (`active`/`draft` state machine, default `active`).
- Brands gain a `handle` (kebab-case, unique, auto-generated from name).
- Basics mirrors the Product prototype's short description + rich-text description
  rather than a single textarea.
- `youtube`/`vimeo` attribute fields render an ID input with a live embed preview
  (matching the Filament admin); `file` is read-only in v1.
- The media edit dialog ships the full prototype scope — alt, caption and focal point —
  stored as `custom_properties` with `focalCrop` mirroring for cropping conversions.

## References

- Design prototype: `/Users/glenn/GitHub/lunarphp/lunar-v2-ui` — `src/pages/BrandsList.vue`,
  `src/pages/BrandEdit.vue`, `src/components/{Media,Attributes,Slugs,CollectionPicker,StatusSegmentedControl}.vue`
- [[0049-inertia-panel]] — panel architecture, Customers/Channels slice
- [[0050-panel-order-history-chart]] — partial-reload chart pattern reused for activity
- [[0051-panel-edit-drafts]] — draft autosave/commit/conflict machinery
- [[0019-attribute-system-redesign]] (completed) — attribute storage the schema serializer reads
- Spec 0021 — state machines (the `BrandState` shape)

## Implementation plan

- [x] Slice 1 — Core: `status` column + `BrandState` + `scopeActive`; `handle` column +
      generation + factory + upgrade backfill; `CreatesBrand` / `UpdatesBrand` /
      `DeletesBrand` (with products guard, Filament delegation); URL and media action
      seams; core tests.
- [x] Slice 2 — Panel scaffold: `CatalogSection`, routes, nav, Brands index (search,
      status filter, sort, pagination, row + bulk actions), create page, edit page with
      Basics (`TranslatedInput`, `RichTextEditor`) + status sidebar on drafts;
      `brands.php` + nav lang keys (16 locales).
- [x] Slice 3 — URL slugs: `UrlSlugs` component, nested endpoints, `urls.php` lang group,
      feature + component tests. (Also fixed core's `ModelManifest` route binders
      bypassing `Route::scopeBindings()` — nested sub-resources previously resolved
      even under the wrong parent record.)
- [x] Slice 4 — Media: `MediaManager` + edit dialog (`FocalPointEditor`, `CropPreview`),
      nested endpoints, custom-property + focal mirroring in the media actions,
      `media.php` lang group, feature + component tests.
- [x] Slice 5 — Attributes: schema serializer + rules builder, `AttributeFields`
      component, draft integration, `attributes.php` lang group, tests.
- [x] Slice 6 — Collections: `CollectionPicker` + search endpoint + sync through
      `UpdatesBrand`, Usage/Activity side cards, `collections.php` lang group, tests.
