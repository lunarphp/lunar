# 0050 — Panel action extension points (row, bulk and page actions)

- Status: accepted
- Author: Glenn Jacobs
- Created: 2026-07-10
- TODO item: Panel action extension points — row/bulk/page actions (spec 0050)

## Problem

Spec 0049 shipped the panel's extension mechanism — sections, navigation, routes, table
columns, and slots — and first-party Customers/Channels screens dogfood all of it. An audit
against both the real package and the design prototype (`lunarphp/lunar-v2-ui`) found the
"actions" side of that mechanism is incomplete in two different ways:

1. **Modeled but unwired.** `packages/panel/src/Tables/TableAction.php` and
   `TableBulkAction.php` are fully implemented abstracts, and `TableExtensionResolver`
   already resolves `getActions()`/`getBulkActions()` for a table id. Nothing consumes
   them: `CustomerIndexController::index()` only calls `getColumns()`/`applyColumnQueries()`
   /`applySearchQueries()`, never `getActions()`/`getBulkActions()`/`applyFilters()`. On the
   Vue side, `DataTableActions.vue` (used by every settings list — `RolesList.vue`,
   `StaffList.vue`, `TaxZonesList.vue`, etc.) hardcodes exactly two buttons, Edit and
   Delete, rather than rendering an arbitrary action list. The prototype's bulk-select
   toolbars (`ProductsList.vue:265-276`, `VariantBulkBar.vue` — "Set active", "Set price",
   "Delete", …) are shape-compatible with `TableBulkAction` but are first-party hardcoded
   Vue, not driven by `bulkActions()`. An add-on can declare a table action or bulk action
   today and it will never render or execute.

2. **Page-header actions are not modeled at all.** The prototype puts a "more actions"
   button/menu in the page header — next to a record's title (`OrderDetail.vue:653-670`:
   `TopBar` `#actions` slot → `DropdownMenu` → items like "Create return", gated by
   `hasReturnableLine`; `CustomerDetail.vue:212-214` has the same trigger shape, `Button
   icon="more"`, though the prototype never wires a menu to it) **and** at the top of a
   listing page (an "Add" / "Import" / "Export" cluster above a table — e.g. an "Import
   Products" button on a products index). Both are the same shape: a labelled,
   permission-gated, optionally-confirmed button or menu item in a page's header. The only
   difference is whether the page has a record to hand the action as context. Nothing in
   `packages/panel/src/` models this — there is no header-action abstract and no `Section`
   hook for it. `SlotRegistry` could host a component in a header zone, but a slot is
   untyped: an add-on would have to hand-roll a Vue component instead of declaring
   `key`/`label`/`url`/`method`/`confirmationMessage`/`permission`, the same ergonomic
   contract `TableAction` already gives table rows.

Both gaps matter because they're the places an add-on realistically needs to hook in next:
"add a button to this row", "add a bulk action across selected rows", and "add a button to
this page's header" (record or listing) are at least as common a customisation need as
adding a table column, which the panel already supports end to end.

Detail-page **tabs** (the prototype's Customer Addresses/Users/Orders/Activity tabs,
`CustomerDetail.vue:64-70`) are deliberately **not** made an extension point — see
Alternatives. An add-on that needs an in-context view registers a route/page (0049) and
links to it from a page action, or injects into an existing slot zone; it does not get to
crowd the first-party tab bar.

## Proposal

### Guiding principle — an always-present ellipsis group

Every page header and every table row renders an ellipsis ("more actions") dropdown as the
default home for actions. The group is rendered only when it contains at least one action,
so a page or row with nothing registered shows no ellipsis at all. This guarantees an add-on
can *always* inject an action into any page or row — the injection point is universal, not
dependent on a first-party page having wired up a slot for it — while keeping the chrome
compact: any number of actions collapse behind one button instead of spreading across the
UI. Actions default into this dropdown; promoting one to an always-visible inline button is
the opt-in exception (`primary()`), reserved mostly for first-party primary actions like
"Add" or "Edit". This applies uniformly to `TableAction` (per row) and `PageAction` (per
header). `TableBulkAction` is the one exception — it lives in the selection toolbar that
appears when rows are checked, not the ellipsis, because it acts on the current selection.

### Guaranteeing the seams — shared page scaffold, coverage test, convention

The 0049 audit found the panel's real weakness: extension points that a page must opt into
end up wired on one page and forgotten on the rest — `customers/Edit.vue` is the only page
in the whole panel with a `<PanelSlot>`, and today every page hand-rolls its own header
markup (`<h1>` plus buttons) directly, so `PanelLayout` guarantees nothing. The universal
ellipsis principle above is only real if pages cannot ship without it. Three layers, in
descending order of reliability:

- **Structural (the actual guarantee).** Move the page header into a shared scaffold —
  either a `PageHeader` component or header slots on `PanelLayout` — that owns the standard
  seams: the primary-action buttons, the always-present page-action ellipsis
  (`<PageActions>`), and a standard set of slot zones rendered for every page:
  `{page}:main:before` and `{page}:main:after`. First-party pages render their header and
  body through the scaffold, so the seams exist by construction; a page cannot omit what it
  no longer hand-rolls. (Typed header actions go through `PageAction`; the two `main` zones
  cover arbitrary add-on content, so the two mechanisms do not overlap.)
- **Coverage test.** A convention test scans `resources/js/pages/**/*.vue` and asserts every
  content page (auth pages under `pages/auth/**` are exempt — they legitimately have no
  extension surface) is built through the scaffold, so a newly added page that skips it
  fails CI rather than silently shipping without seams. This guards the structural rule
  against erosion.
- **Convention.** The panel package's `CLAUDE.md` documents "building a panel page" — always
  go through the scaffold, name slot zones `{page}:{region}[:position]`, register actions
  rather than hard-coding buttons. This is guidance for humans and for Claude; it is a
  section in the existing conventions file, not a new doc.

### Ordering — reuse the existing `Position` value object

For a cross-package extension point, placement has to be expressible against something
stable. A bare numeric priority is not: an add-on wanting its action to sit immediately
after "Edit" must guess a number, and that guess breaks when first-party renumbers or when
two add-ons collide on the same value.

0049 already ships the right primitive: `Lunar\Panel\Tables\Support\Position`, with
`first()`, `last()`, `before(key)`, `after(key)` and `priority(int)`. `TableColumn` returns
one from `position()`. **But nothing applies it** — `TableExtensionResolver` never sorts by
position and the JS never reads it, so a column's declared position is currently inert and
columns render in registration order. This spec builds the missing resolution and reuses the
existing primitive rather than inventing a parallel `priority`/`before`/`after` triple.

- Every ordered entry — navigation items and all action/column types — exposes a
  `position(): Position` (default `Position::priority(50)`, preserving today's behaviour).
- A shared `OrderResolver` sorts a list of positioned entries: apply numeric priority first
  (lower first, ties keep registration order via a stable sort), then reposition
  `before`/`after` entries adjacent to their target key. If the target key is absent the
  anchor is ignored and the entry keeps its priority slot (a warning is logged, consistent
  with 0049's unknown-section-key behaviour). Circular anchors fall back to priority.
  Entries anchored to the same target order among themselves by priority.
- Move `Position` from `Tables\Support` to `Lunar\Panel\Support\Position` now that
  navigation, columns and actions all depend on it — it is no longer a tables-only concern.
  0049 is unmerged (stacked below this branch), so this rename costs nothing downstream.

The one shared `OrderResolver` is used by the navigation registry, the table-extension
resolver (which finally applies column ordering), and the new action resolvers — one
ordering model across the panel, not one per extension point.

Navigation (`NavigationRegistry`, shipped in 0049) is retrofitted from its bare
`int $priority` to a `Position`. Existing default-priority items are unchanged in behaviour,
but the constructor of `NavigationItem`/`NavigationGroup` changes, so it is called out in
Migration impact.

### Wire the existing `TableAction` / `TableBulkAction` seam (no new PHP abstracts)

- `CustomerIndexController::index()` (and any future table-backed index controller) calls
  `TableExtensionResolver::getActions()`/`getBulkActions()` alongside the existing
  `getColumns()` call, and shares the resolved lists as Inertia props (`tableActions`,
  `tableBulkActions`) the same way columns already are.
- Generalise the wiring so it isn't Customers-only: extract the "resolve columns + actions
  + bulk actions + apply search/filter queries for a table id" sequence used by
  `CustomerIndexController` into a small reusable helper (a trait or a
  `ResolvesTableExtensions` concern) and apply it to `ChannelsController`'s index method
  too, so table extension is a real cross-cutting pattern, not a one-table proof of
  concept.
- Vue: replace `DataTableActions.vue`'s hardcoded Edit/Delete pair with a component that
  renders `props.rowActions` per the ellipsis principle above — non-primary actions collapse
  into the row's ellipsis dropdown (hidden when empty), primary ones (first-party Edit)
  render inline. First-party Edit/Delete become ordinary `TableAction` entries registered by
  the first-party `Section`, so there is only one code path. Add a `BulkActionsToolbar.vue`
  that renders `props.tableBulkActions` in the selection toolbar when rows are checked,
  replacing the bespoke bulk bars the prototype hardcodes per page — a bulk action acts on
  the set of selected record ids (the one piece of table state any action sees).
- `TableAction`/`TableBulkAction` gain a `position(): Position` accessor (as above), so an
  add-on row action can sit immediately after first-party `edit`.
- `applyFilters()` and filters generally are out of scope for this spec; this spec only
  wires actions.

### Add a `PageAction` extension point (record and listing headers)

Record-header actions ("Create return" on an order) and listing-header actions ("Import
Products" above the products table) are the same shape and become **one** extension point,
keyed by page id. A record page hands the action its record as context; a listing page
hands it none.

- **PHP**: `Lunar\Panel\Actions\PageAction` (abstract, mirrors `TableAction`'s shape):
  `key()`, `label()`, `icon()`, `url(mixed $context = null)`, `method()` (default `get`),
  `confirmationMessage()`, `permission()`, `primary()` (default `false`),
  `position(): Position` (the ordering primitive above), `visible(mixed
  $context = null)`. `$context` is the record on a record page (`Customer $record` →
  `/customers/5/impersonate`) and `null` on a listing page (`/products/import`). A
  `PageAction` never receives table query, filter or selection state — that is not relevant
  to a header action; only a `TableBulkAction` sees the selection. `primary()` selects the
  tier per the ellipsis principle: `false` (the default) collapses the action into the
  header's ellipsis dropdown, `true` promotes it to an always-visible inline button (e.g.
  "Add Product"). Registered via a new `Section::pageActions(): array` hook, keyed by page
  id (`customers.edit`, `products.index`), resolved by a `PageActionResolver` (same shape as
  `TableExtensionResolver`, one resolver, no new registry class).
- **Sharing**: `HandlePanelInertiaRequests` resolves page actions for the current page id
  the same way it already resolves slot entries, sharing a permission-filtered,
  priority-ordered `pageActions` prop.
- **Vue**: a `<PageActions :actions="pageActions" />` component dropped into `TopBar`'s
  `#actions` slot renders primary entries as inline buttons and collapses the rest into an
  ellipsis `DropdownMenu`, shown only when non-empty (matching the prototype's
  `OrderDetail.vue` pattern). Destructive actions (`confirmationMessage()` present) route
  through the existing `ConfirmDialog.vue`. Used on both record pages (`customers/Edit.vue`)
  and listing pages.
- First-party Customers registers nothing new here initially (its Edit/Delete already live
  elsewhere on the page) — the mechanism ships proven by the example add-on (see
  Implementation plan), matching how slots shipped in 0049.

### Naming and consistency

All additions follow 0049's existing pattern: a small abstract with a resolver, a `Section`
hook to register it, an Inertia-shared prop, and a Vue component that renders the resolved
list generically. No new registry concepts, no new zone-naming scheme — this is filling in
the "actions" corner of the pattern 0049 already established, not inventing a second
mechanism.

## Alternatives considered

- **Model page actions as generic `SlotRegistry` zones instead of a typed abstract.**
  Rejected: a slot is "render this arbitrary component here," which is right for open-ended
  content (0049's SEO-section example) but wrong for something that is structurally always
  "a labelled, permission-gated, optionally-confirmed link or button" — forcing every
  add-on to hand-roll a dropdown item as a Vue component is exactly the ergonomic regression
  `TableAction` was built to avoid for table rows.
- **Keep `RecordAction` and a listing-page action as two separate concepts.** Rejected:
  they are the same shape — a header button/menu-item differing only in whether the page
  has a record to pass as context. One `PageAction` keyed by page id, with an optional
  `$context`, covers both with less surface than two abstracts.
- **A `tabs()` extension point letting add-ons register detail-page tabs.** Rejected on
  design grounds, not scope. Columns, row actions and bulk actions are additive to a
  list that grows gracefully; a tab bar is a bounded, curated navigational structure where
  every addition costs shared horizontal space and cognitive load (Filament's add-on tab
  sprawl is the cautionary case). The legitimate need — an add-on subsystem wanting an
  in-context view of a record — is already served by 0049: register a route/page and link
  to it from a `PageAction`, or inject content into an existing slot zone. Keeping the tab
  set first-party-only preserves a coherent page. It can be added later, additively, if
  real demand appears.
- **Ship filters/`applyFilters()` in this spec too, since they're the other unwired
  `TableExtension` method.** Rejected: filters are a separate concern with their own UI
  affordance (chips, popovers, saved-filter state) and are not part of this spec.
- **Do nothing until an add-on developer actually needs this.** Rejected: the prototype
  already shows the intended shape (`OrderDetail.vue`'s action menu, listing-header
  buttons, bulk toolbars), and 0049 explicitly reserved "actions" as part of
  `TableExtension`'s stated purpose — this is completing a scoped commitment, not
  speculative extension.

## Migration impact

- No database migrations.
- Additive to the public contract surface: new `PageAction` abstract, new
  `Section::pageActions()` hook, new Inertia props (`tableActions`, `tableBulkActions`,
  `pageActions`), and a `position(): Position` accessor on `TableAction`/`TableBulkAction`.
- Moves `Position` from `Lunar\Panel\Tables\Support` to `Lunar\Panel\Support` and changes
  `NavigationItem`/`NavigationGroup` from an `int $priority` to a `Position`. Both are 0049
  surface that is unmerged (stacked below this branch), so there is no released contract to
  break; the 0049 PR and this one land together.
- First-party pages are refactored to render their header/body through the shared scaffold
  (new `PageHeader`/`PanelLayout` header slots). This is internal to the panel package — no
  public JS API is removed — but every existing content page's template changes, and the
  standard `{page}:main:before`/`{page}:main:after` slot zones become available panel-wide.
- Translation impact: new first-party strings (e.g. "More actions" aria-label) need all 16
  locales (`ar, bg, de, en, es, fa, fr, hr, hu, mn, nl, pl, pt_BR, ro, tr, vi`).
- No Filament impact — panel-only, Filament's own actions library (spec 0009) is unrelated
  and unchanged.

## Open questions

- None outstanding.

## References

- [[0049-inertia-panel]] — the extension mechanism this spec completes.

## Implementation plan

- [ ] Slice 1 — Move `Position` to `Lunar\Panel\Support`. Build the shared `OrderResolver`
      (priority, then `before`/`after` anchors; missing-target fallback with warning;
      circular-anchor guard) with unit tests for ordering and fallback cases. Apply it in
      `TableExtensionResolver::getColumns()` so column `position()` is finally honoured
      (fixing the latent 0049 gap). Retrofit `NavigationItem`/`NavigationGroup` to a
      `Position` and route the registry's sorting through `OrderResolver`. Add
      `position(): Position` to `TableAction`/`TableBulkAction`.
- [ ] Slice 2 — Wire `TableAction`/`TableBulkAction` into Customers: resolver calls in
      `CustomerIndexController`, `tableActions`/`tableBulkActions` Inertia props,
      `DataTableActions.vue` and new `BulkActionsToolbar.vue` render from props instead of
      hardcoded buttons. Row actions collapse into the per-row ellipsis. First-party
      Edit/Delete become registered actions, not bespoke markup.
- [ ] Slice 3 — Extract the resolve-and-share sequence into a reusable concern and apply it
      to `ChannelsController`, proving the pattern is cross-cutting.
- [ ] Slice 4 — `PageAction` abstract (with `primary()` tier and optional `$context`),
      `PageActionResolver`, `Section::pageActions()` hook, `pageActions` Inertia prop, and a
      `PageActions.vue` header component (primary buttons + always-present overflow ellipsis).
- [ ] Slice 5 — Shared page scaffold: extract the per-page header into a `PageHeader`
      component (or `PanelLayout` header slots) that owns the primary buttons, the
      `<PageActions>` ellipsis, and the standard `{page}:main:before`/`{page}:main:after`
      slot zones. Refactor every content page (`customers/*`, `settings/channels/*`,
      `Dashboard`) onto it. Add the convention test asserting each content page (excluding
      `pages/auth/**`) is built through the scaffold. Document the page-building convention
      in the panel package `CLAUDE.md`.
- [ ] Slice 6 — Update `examples/panel-addon-example` to register one of each — a Customers
      row action, a bulk action, a record-page action (on `customers.edit`), a listing-page
      action (on a listing page), and a slot entry into a standard `:main` zone — at least
      one action placed with a `before`/`after` anchor. Proves all the action extension
      points, relative ordering, and panel-wide slot zones end to end the way the existing
      example already proves columns.
