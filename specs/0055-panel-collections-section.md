# 0055 — Panel Collections section

- Status: implemented
- Author: Glenn Jacobs
- Created: 2026-07-20
- TODO item: Panel Collections section — group tree, hierarchy, curated products, availability (spec 0055)

## Problem

The panel's Catalog side ships Brands only (spec 0052). Collections is the design
prototype's other catalog resource (`lunar-v2-ui`: `CollectionsList.vue`,
`CollectionEdit.vue`) and the last shared-surface consumer before the much larger
Products build. Its edit screen reuses everything Brands introduced — media manager,
attribute fields, URL slugs, drafts — but adds four capabilities the panel has no
surface for yet:

- **Collection groups** — the list page is not a flat table but a tree of collections
  bracketed by `CollectionGroup`, with inline group create/rename/delete. The panel has
  no group management at all.
- **Hierarchy** — collections are a nested set (`NodeTrait`); the prototype's list is an
  expandable tree and the edit page has group/parent pickers. No panel component renders
  a tree, and no endpoint moves a node.
- **Curated products** — the `collection_product` pivot carries `position` and the
  `sort` column drives `SortProducts` (`custom`, `min_price:asc|desc`, `sku:asc|desc`).
  The prototype curates products with a picker dialog. The panel has no product picker
  and no product search endpoint.
- **Availability** — the prototype's `SideAvailability` card schedules channels and
  customer groups. Core supports both (`HasChannels` pivot `enabled`/`starts_at`/`ends_at`;
  `collection_customer_group` pivot adds `visible`), but neither admin nor panel exposes
  collection availability outside Filament's relation managers.

Unlike Brands, most of the core model is already in place: collections have a
`status` state machine (`Draft`/`Published`/`Archived`, spec 0021), dedicated translated
`name`/`short_description`/`description` (spec 0018), and action seams for create, move,
delete and product sorting. Four prototype/core disagreements still need settling:

- The prototype shows a **handle**; `collections` has no handle column (collection
  *groups* have one).
- The prototype's status vocabulary is **active/draft**; core is
  **draft/published/archived**.
- The prototype **creates an "Untitled collection" record instantly** when "New
  collection" is clicked; Brands settled on a minimal create page.
- The prototype **re-homes** a deleted group's collections into the first remaining
  group; Filament refuses to delete a non-empty group.

There is also no `UpdatesCollection` action (only create/move/delete exist), no
collection-group actions at all, and `Collection` lacks `LogsActivity` (Brand and
Product both log), so the edit page's Activity card would have nothing to show.

## Proposal

Collections list/create/edit screens in the existing `CatalogSection`, matching the
design prototype and following the Brands architecture (core action delegation, edit
drafts, table-extension row actions, immediate sub-resources for media/URLs). New
shared surfaces: a tree list, a product picker + search endpoint, and an availability
card — each built for reuse by the later Products work.

### Core additions

**Collection handle.** `handle` unique string column on the `collections` baseline
migration (alpha fold-in), for the same reason brands got one (spec 0052): storefront
code and seeders need a stable programmatic key that survives renames and isn't
per-language. Kebab-case via `Str::slug($name)` — the convention brands settled;
the prototype's snake_case handles are a prototype-ism. Auto-generated with
numeric-suffix uniqueness by the model's creating hook (the brands convention) when
not supplied, with a replicating hook clearing the handle on duplication;
`CollectionFactory` derives it from the name; the upgrade package backfills v1
collections from their default-language name.

**Create actions accept attributes.** `CreatesRootCollection` and
`CreatesChildCollection` currently take only a name. Both gain an optional
`array $attributes = []` parameter (handle, status, translated descriptions) merged
into the `Collection::create()` payload, with the handle generated from the name when
absent. Additive to the contracts; alpha rule applies.

**`UpdatesCollection`.** New contract/action (`Contracts/Actions/Collections/`,
`Actions/Collections/`, registered in `ActionServiceProvider`): updates fields
(name, handle, status, translated descriptions, `sort`), attribute data, syncs the
availability pivots (see the availability surface below), and syncs `products()`
membership is **not** in its remit — product curation runs through its own endpoints
(below). This is the draft-commit seam, mirroring `UpdatesBrand`.

**Collection-group actions.** New `Contracts/Actions/CollectionGroups/` +
`Actions/CollectionGroups/`: `CreatesCollectionGroup`, `UpdatesCollectionGroup`
(name + handle), `DeletesCollectionGroup` — refuses (domain exception) while the group
has collections, moving the guard that today lives only in Filament's
`EditCollectionGroup::before()` into the seam; the Filament page keeps its notification
but delegates the check, exactly as `DeletesBrand` did.

**`LogsActivity` on `Collection`.** Trait added so the Activity side card has data,
matching Brand and Product.

**Cross-group moves.** `MovesCollection` gains an optional `?CollectionGroup $group`
parameter (the cycle guards stay): same-group moves ride the nested-set trait as
before, while a group change re-scopes the whole subtree and rebuilds both groups'
trees from their `parent_id` links (`fixTree`), recording cache invalidations for the
node and its descendants. `DeletesCollection` (re-parent-or-refuse for descendants) is
reused as-is. The vestigial `type` column (`static` default, never read outside the
schema) is out of scope.

### Panel server side

**`CatalogSection`** gains a Collections navigation item (folder icon, after Brands)
and a second permission constant: `catalog:manage-collections` — the handle Filament's
`CollectionResource`/`CollectionGroupResource` already gate on; no new permission.
Registers `collections.index` in `tableExtensions()` (row actions on tree rows resolve
through the same extension pipeline as table rows, so add-ons can inject) and
`CollectionDraftResource` in `draftables()`.

**Routes** — prefix `collections`, names `panel.collections.*`, middleware
`can:catalog:manage-collections`:

- `index`, `create`, `store`, `edit`, `update`, `destroy`, the three standard
  `draft.*` routes.
- `move` (`PUT /{collection}/move`) — group/parent changes, applied immediately.
- Nested `media.store|update|destroy|reorder` and `urls.store|update|destroy` under
  `Route::scopeBindings()`, reusing the brand controllers' shape and the spec 0052
  media/URL actions.
- `products.attach|detach|reorder` — immediate product-curation endpoints.
- `groups.store|update|destroy` — collection-group management.
- `catalog/products/search` (`panel.catalog.products.search`) — lightweight product
  lookup (id, name, SKU, thumbnail, brand name, status) mirroring
  `CollectionSearchController`, reusable by the later Products/Discounts work.
  The existing `collections.search` endpoint gains optional `group_id` and `exclude`
  (id + its descendants) parameters for the parent picker.

**Controllers** (`Http/Controllers/Collections/`), delegating writes to core actions:

- `CollectionIndexController` — returns collection groups (id, name, handle, count)
  each with its tree: ordered nested rows (id, name, handle, thumbnail `small` URL,
  status, products count, children ids), depth-first from the nested set. Search and
  status filter run server-side: a matching set is computed (name/handle/URL-slug
  match + status), then expanded with every ancestor so each match stays reachable in
  the tree — the prototype's `visibleIds` logic moved to the server. When filtering,
  the payload marks rows as `matched` vs ancestor-context so the client can show the
  "N of M" count of true matches. No pagination — the tree is returned whole per group;
  collections number in the hundreds, not the tens of thousands, and the rows are thin.
- `CollectionCreateController` — create page + store: name, group (required, select),
  parent (optional, scoped to the group), status (segmented, default `draft` — the
  core default; collections gate storefront content like products do, unlike brands).
  Store calls `CreatesRootCollection`/`CreatesChildCollection` and redirects to edit.
- `CollectionEditController` — renders `collections/Edit` with: collection fields,
  `draft`, `attributeSchema` + values, `media`, `urls` + `languages`, group list +
  current parent summary (id/name/breadcrumb), paginated product summaries (partial
  reload), availability rows (channels and customer groups with pivot state),
  descendants count, recent activity, and the route map. `update`/`destroy` as brands;
  destroy passes an optional `reparent` flag (see delete flow).
- `CollectionMoveController` — validates the target group/parent (parent must be in
  the target group; `MovesCollection` guards self/descendant cycles), re-homes to the
  new group's root level when only the group changes. Immediate, not drafted: nested-set
  moves are structural, visible to every other staff member's tree, and can't merge as
  field-level draft conflicts.
- `CollectionProductsController` — `attach` (ids from the picker; appended at the end
  of the `position` sequence), `detach`, `reorder` (ordered id list for the current
  page, rewritten into that page's position window; drag reorder is only offered when
  `sort` is `custom`). Immediate sub-resources, like media — curated sets can be large,
  so membership is paginated and persisted per operation rather than drafted.
- `CollectionGroupController` — store/update/destroy through the new group actions;
  destroy surfaces the non-empty guard as a flash error.

**Requests** (`Http/Requests/Collections/`): `CollectionRequest` (name required,
handle nullable slug-format unique, status in-state-list, translated description maps,
`sort` in the allowed token list, attribute values against the schema, availability
row shapes), `CollectionMoveRequest`, `CollectionGroupRequest` (name required, handle
nullable unique), `CollectionProductsRequest`, plus the brand-shaped media/URL requests.

**`CollectionDraftResource`** — draftable fields: `name`, `handle`, `status`,
`short_description`, `description`, `sort`, per-attribute keys (`attribute:{handle}`),
and per-row availability keys (`channel:{id}`, `customer_group:{id}` — the panel's
colon-prefix draft-key convention — each a small object of
`enabled`/`visible`/`starts_at`/`ends_at` serialized by a new
`Support/AvailabilitySchema`), so two staff scheduling different channels never
conflict. `rules()` delegates to `CollectionRequest`; `commit()` to
`UpdatesCollection`, rebuilding the full pivot maps from the stored rows overlaid
with the drafted ones so untouched rows survive the sync. Media, URLs, product
membership and hierarchy moves are immediate, outside the draft.

**`CollectionsTableExtension`** — row actions rendered on tree rows through the shared
`RowActions` component: `EditCollectionAction`, `AddChildCollectionAction` (links to
the create page with group + parent preselected), `DeleteCollectionAction`. The delete
confirmation states what happens to children: when the collection has descendants the
dialog offers "promote children to this collection's parent", passing
`DeletesCollection`'s re-parent target; without the flag a descendant-bearing delete is
refused server-side. No bulk actions — the prototype's tree has no row selection.

### Shared surface — collection tree

`CollectionTree.vue` + `CollectionTreeRow.vue`, per the prototype: group brackets as
collapsible headers (uppercase label, count, "add collection in group" and "rename
group" hover actions), rows with thumbnail/initial, name + mono handle, truncated
short description, descendant + product counts, status badge, and the row-actions
ellipsis. Chevron expand/collapse per row; group open state persists in
`localStorage`, row state is in-memory with roots defaulting open. While a search or
status filter is active every visible node is force-expanded and chevrons disabled so
matches are never hidden behind a collapsed parent. Empty states per group ("no
collections in this group yet" with an inline add link, "no matches in this group")
and a page-level `PageEmpty` when nothing matches anywhere.

`CollectionGroupDialog.vue` — create/rename (name + handle, handle auto-synced from
name until manually edited) and delete, with the delete control disabled (with an
explanatory hint) while the group has collections.

### Shared surface — product picker

`ProductPicker` section + `ProductPickerDialog.vue`, per the prototype: a table of the
curated products (thumbnail, name + mono SKU, brand, status badge, remove button),
paginated; "Add product" opens a search dialog backed by `products.search` (rows show
thumbnail, name, SKU, status; multi-select; already-attached ids disabled). A **sort
rule** select above the table exposes the core `sort` tokens — custom (drag to
reorder), price low→high / high→low, SKU A→Z / Z→A — as a drafted field; the table
rows are drag-reorderable only while the (saved) rule is `custom`.

### Shared surface — availability card

`AvailabilityCard.vue`, the prototype's `SideAvailability`: a side card with two
collapsible sections — sales channels and customer groups — each row carrying a power
pill (enabled) and a calendar pill (schedule). Collapsed sections summarise as
All/Some/None with a tone dot and a scheduled-count chip. Scheduling a row opens a
small popover with a start date (the prototype's "turns on") and an optional end date —
the pivots carry both, and hiding `ends_at` would make an end date set elsewhere
invisible and uneditable. A scheduled row shows the "Turns on {date}" line and its
power pill locks until the schedule is cleared. Customer-group rows add the prototype's
eye toggle, mapped to the pivot's `visible` flag: an enabled-but-hidden group can reach
the collection by direct link but doesn't see it in navigation ("Hidden from
browsing" replaces the prototype's product-flavoured "view only" copy).

Values bind to the draft's per-row availability keys, so autosave, dirty tracking and
conflict handling come from `useEditDraft`; `UpdatesCollection` syncs the pivots on
commit. Strings live in a new shared `availability.php` lang group. Products adopts
the same card later.

### Pages

`pages/collections/{Index,Create,Edit}.vue`, standard scaffold (`PanelLayout` +
`PageHeader` + `PageZone` before/after, enforced by `PageScaffoldTest`):

- **Index** — header actions "New collection" (primary) and "New group"; toolbar
  (debounced search, status `FilterDropdown` over the core states —
  published/draft/archived, the prototype's "active" rendered as Published — and the
  matched count), then the grouped `CollectionTree` and `CollectionGroupDialog`.
- **Create** — name, group select, parent picker (scoped to the chosen group,
  cleared when the group changes), status segmented control; posts to `store`,
  redirects to edit.
- **Edit** — two-column. Main: Basics (name + handle row with auto-sync, translated
  single-line short description with character hint, `RichTextEditor` description —
  the Brands layout), Hierarchy (group select + parent picker with the "changing the
  group resets the parent" hint; applying a change confirms and hits the `move`
  endpoint immediately, noting the subtree moves too), `MediaManager`,
  `AttributeFields`, Products (sort rule + curated table + picker dialog), `UrlSlugs`.
  Sidebar: Status (`StatusSegmentedControl`, drafted, three states), Usage (products
  count + descendants count), `AvailabilityCard`, Activity (`ActivityTimeline`).
  Save cluster is `DraftActions` + `DraftConflictDialog` via `useEditDraft`.

The parent picker (`ParentCollectionPicker`) is a searchable combobox over
`collections.search` scoped by `group_id` and excluding the collection itself and its
descendants. The prototype's "Duplicate" page action and AI description generation
remain out of scope, as they were for Brands.

### Translations

The existing `collections.php` group (introduced by spec 0052 for the picker) grows
the section's strings; new shared `availability.php` and `products.php` (picker
strings) groups; `nav.php` gains `collections`. English first, mirrored across all
16 locales, landing with the slice that introduces each group.

### Testing

- **Pest (`tests/panel/Feature/Collections/`)**: index (tree shape and ordering,
  search returns matches plus ancestors with matched flags, status filter, group
  counts, permission gating), create (root and child, group/parent validation),
  edit props (schema, media/urls/products/availability payloads), draft lifecycle
  (scalar field, `attribute.{handle}`, `sort`, an `availability.*` row — autosave,
  commit, 409 conflict, rebase), move endpoint (re-parent, group change re-homes and
  resets parent, self/descendant cycle rejection), product endpoints (attach appends
  positions, detach, reorder window, reorder rejected when sort is not custom),
  group endpoints (create, rename, non-empty delete guard), destroy (leaf delete,
  descendant-bearing delete refused without the flag / promoted with it), media and
  URL endpoints smoke (shared controllers, brand-proven).
- **Pest (`tests/core/`)**: `UpdatesCollection` (fields, attribute data, availability
  sync), group actions (delete guard + Filament delegation), create actions'
  attributes parameter + handle generation/uniqueness, `LogsActivity` on Collection.
- **Vitest**: `CollectionTree`/`CollectionTreeRow` (expand state, filtering,
  force-expand), `CollectionGroupDialog`, `ParentCollectionPicker`,
  `ProductPickerDialog`, `AvailabilityCard` (summaries, schedule lock, visible
  toggle).
- `PageScaffoldTest` covers the new pages automatically. PHPStan + Pint as required.

## Alternatives considered

- **Prototype's instant "Untitled collection" create**: rejected — it writes a record
  on a stray click and litters the tree with untitled drafts; the Brands create-page
  precedent (minimal fields, redirect to edit) keeps parity across catalog sections.
- **Renaming core states to match the prototype's active/draft**: rejected — the
  `Draft`/`Published`/`Archived` machine shipped in spec 0021 and products share the
  vocabulary; the panel presents the core states and the prototype's "Active" simply
  reads "Published".
- **Drafting hierarchy moves** (group/parent as draft fields, committed via
  `MovesCollection`): rejected — moves restructure a tree other staff are looking at,
  can't be merged field-wise, and a stale draft could commit a move into a node that
  has since been re-parented; an immediate, validated endpoint with a confirmation is
  honest about the semantics.
- **Group delete re-homes collections** (the prototype's behaviour): rejected — a
  silent mass re-parent is destructive and surprising; Filament already refuses, and
  the seam (`DeletesCollectionGroup`) enforces the same guard everywhere. Staff move
  collections first, deliberately.
- **Drafting product membership as an ordered `product_ids` array** (the brands
  `collection_ids` pattern): rejected — curated sets can run to hundreds of products,
  which needs pagination; a drafted array requires the full set client-side and turns
  every attach into a whole-list conflict surface. Immediate endpoints follow the
  media/addresses precedent.
- **Client-side tree filtering over a full payload** (the prototype's approach):
  rejected for the controller — search semantics (match + ancestors) belong on the
  server where they can hit indexes and stay consistent with other sections' server
  toolbars; the payload is still the whole tree, so the interaction feels identical.
- **A `positions` bulk-rewrite reorder across pages**: deferred — drag reorder within
  the current page covers the curation flow; cross-page moves are a follow-up if real
  usage demands them.
- **A new `catalog:manage-collections` panel-specific permission**: not needed — the
  handle already exists on the Filament resources; the panel matches it so one staff
  role behaves identically in both admins (the Brands decision).

## Migration impact

- `collections` baseline migration gains `handle` (unique) — alpha fold-in; the
  upgrade package backfills v1 collections with name-derived handles.
- `CreatesRootCollection`/`CreatesChildCollection` contracts gain an optional
  `$attributes` parameter — additive but a contract-surface change; alpha rule, noted
  for the upgrade package's docs.
- New core contracts/actions (`UpdatesCollection`, three collection-group actions) are
  additive. `DeletesCollectionGroup` centralises the non-empty guard Filament already
  enforces; as with `DeletesBrand`, the guard also attaches to the model's `deleting`
  hook so consumer-code deletes can't strand collections.
- `Collection` gains `LogsActivity` — additive; activity rows start accruing on
  upgrade.
- Translations: `availability.php` and `products.php` groups plus `collections.php`
  and `nav.php` additions, all 16 locales.
- Filament admin: `EditCollectionGroup`'s delete guard delegates to
  `DeletesCollectionGroup`; no UI change. Collection availability/media/URL behaviour
  untouched.
- No new npm dependencies — drag reorder reuses the media manager's approach.

## Open questions

None outstanding. Resolved during review:

- Handle casing is kebab-case (`Str::slug`), the brands convention — the prototype's
  snake_case handles were a prototype-ism.
- The customer-group eye toggle maps to the pivot's `visible` flag with
  "hidden from browsing" semantics.
- The schedule popover exposes `ends_at` alongside the prototype's start-only
  "turns on".

## References

- Design prototype: `/Users/glenn/GitHub/lunarphp/lunar-v2-ui` —
  `src/pages/CollectionsList.vue`, `src/pages/CollectionEdit.vue`,
  `src/components/{CollectionTreeRow,CollectionGroupDialog,CollectionGroupPicker,ParentCollectionPicker,ProductPickerDialog,SideAvailability}.vue`
- [[0049-inertia-panel]] — panel architecture
- [[0051-panel-edit-drafts]] — draft autosave/commit/conflict machinery
- [[0052-panel-brands-section]] — shared catalog surfaces this section reuses;
  the architectural template throughout
- [[0021-state-machines]] (completed) — the `CollectionState` machine
- [[0019-attribute-system-redesign]] (completed) — attribute storage the schema
  serializer reads

## Implementation plan

- [x] Slice 1 — Core: `handle` column + generation + factory + upgrade backfill;
      `LogsActivity` on Collection; `$attributes` on the create actions;
      `UpdatesCollection`; collection-group actions with the delete guard and
      Filament delegation; core tests. (Also landed here: the `MovesCollection`
      cross-group extension.)
- [x] Slice 2 — Panel scaffold + list: nav item, routes, `CollectionIndexController`
      (tree payload, search + ancestors, status filter), `CollectionTreeRow`,
      group dialog + endpoints, create page, row actions + delete flow; lang keys
      (16 locales). (Landed together with slice 3 — the list's row links anchor on
      the edit page.)
- [x] Slice 3 — Edit page core: Basics, status sidebar, Usage, Activity,
      `CollectionDraftResource` + draft lifecycle, `AttributeFields` wiring;
      Hierarchy section + `move` endpoint + `ParentCollectionPicker`
      (search endpoint `group_id`/`exclude` params).
- [x] Slice 4 — Media + URLs: nested endpoints reusing the spec 0052 actions and
      components; feature tests.
- [x] Slice 5 — Products: `products.search` endpoint, curated table + sort rule +
      drag reorder, `ProductPickerDialog`, attach/detach/reorder endpoints,
      `products.php` lang group, tests.
- [x] Slice 6 — Availability: `AvailabilityCard` + `AvailabilitySchema`, draft
      integration, pivot sync in `UpdatesCollection`, `availability.php` lang
      group, tests.
