# 0049 — Inertia admin panel (`lunarphp/panel`)

- Status: implemented
- Author: Glenn Jacobs
- Created: 2026-07-09
- TODO item: Inertia admin panel — new `lunarphp/panel` package (spec 0049)

## Problem

The v2 admin is Filament, which ties Lunar's back office to Filament's stack and visual
language. The project direction is a first-party admin panel built on Inertia.js + Vue with a
bespoke design system and — critically — a runtime extension mechanism that lets add-on
packages inject pages, navigation, table columns/actions, and screen sections **without the
consuming app recompiling any JavaScript**. Filament cannot deliver that model for a
Vue-based panel.

Core already anticipates this: `packages/core/config/staff.php` documents the `staff` guard
as shared between Filament and "the future Inertia panel". Two prototypes exist and are
validated:

- **Design prototype** (`lunarphp/lunar-v2-ui`) — a static Vue 3 SPA containing the complete
  visual system (Tailwind v4 OKLCH tokens, Reka UI, Geist, dark mode) and high-fidelity
  screens including Login (two-step with 2FA), Customers, the Settings layout, and Channels.
- **Architecture prototype** (`lunar2` app, `lunarphp/panel` + `catalog` + `addon_example`
  packages) — a working Inertia panel package proving the extension mechanism end to end:
  PHP registries (sections, navigation, table extensions) shared to the frontend via Inertia
  props, a `window.LunarPanel` JS runtime registry, and add-ons shipping independently
  compiled IIFE bundles that share the panel's Vue runtime.

Neither prototype is production code. This spec defines the real package: the architecture
prototype's mechanics with the design prototype's UI, scoped to an initial vertical slice.

## Proposal

Create a new sub-package `packages/panel` (`lunarphp/panel`, namespace `Lunar\Panel\`),
registered like every other sub-package via the monorepo `composer.json` autoload and
`extra.laravel.providers`. It depends only on `core`. The Filament admin remains installed
and untouched; the panel is its intended successor and the two coexist during the
transition. The strategy is **lift the architecture, rebuild the UI**: the PHP layer and the
TypeScript extension runtime port from the architecture prototype largely as-is (adapted to
this monorepo's conventions); every Vue component and screen is rebuilt from the design
prototype. No visual element from the architecture prototype is carried over.

Initial scope: authentication (including 2FA), the full extension surface (navigation,
slots, table columns, row/bulk/page actions, and a shared ordering primitive), the settings
layout, one CRUD implementation (Customers), and one settings section (Channels).

### Package structure

```
packages/panel/
  composer.json
  config/panel.php            merged as lunar.panel
  src/
    PanelServiceProvider.php
    PanelManager.php          singleton behind the Panel facade
    Facades/Panel.php
    Sections/                 Section, SectionExtension, first-party sections
    Navigation/               NavigationRegistry, NavigationGroup, NavigationItem
    Slots/                    SlotRegistry, Slot
    Tables/                   TableExtension, TableColumn/Filter/Action/BulkAction, resolver
    Http/                     controllers, middleware, form requests
    Console/Commands/         lunar:panel:link
  routes/                     auth.php, web.php
  resources/
    views/app.blade.php       Inertia root view
    lang/                     16 locales
    js/                       TypeScript + Vue source
    css/                      Tailwind v4 theme (tokens from the design prototype)
    package/                  vite-plugin.js + package.json exported to add-on authors
  public/build/               prebuilt panel assets
```

The `lunar.panel` config key is currently occupied by the Filament admin
(`packages/admin/config/panel.php`). That file is renamed to `config/admin.php` (merged as
`lunar.admin`) so the new package owns `lunar.panel`: panel path prefix (default `panel`),
guard override (defaults to `lunar.staff.guard`), middleware, and menu grouping.

### Extension architecture — PHP side

Ported from the architecture prototype, adapted to this monorepo's provider conventions
(`$configFiles` array + `mergeConfigFrom`, no third-party package-tools dependency).

- **`PanelManager`** — a container singleton behind the `Panel` facade. Registration hub for
  sections, section extensions, slots, and add-on Vite modules (the single asset pipeline —
  there is deliberately no separate plain script/style registration).
  Resolves the panel guard. Registration happens in add-on service providers' `boot()`;
  processing is deferred to `$app->booted()` so registration order never matters.
- **`Section`** (abstract) — a cohesive admin area. Declares `key()` and optionally
  `label()`, `navigation(NavigationRegistry $nav)`, `settingsNavigation(...)`, `routes()`,
  `tableExtensions()`, `slots(SlotRegistry $slots)`, and `vite()`. Registered via
  `Panel::section(new CustomersSection)`.
- **`SectionExtension`** (abstract) — same optional hooks plus `extends()` returning the
  target section key. Lets an add-on graft navigation, routes, table extensions, and slot
  entries onto an existing section. Unknown section keys log a warning rather than throw.
- **`NavigationRegistry`** — groups, top-level items, and children carrying a `Position`
  (see Ordering below; `priority` stays as an ergonomic shortcut deriving
  `Position::priority($priority)`). Items carry `key`, `label`, `icon`, `route`, `exact`,
  `permission`, `children`. `toArray($user)`
  filters by `$user->can($permission)`, so the sidebar is permission-aware for free. A
  parallel settings-navigation registry drives the settings sidebar. The panel seeds the
  Dashboard item itself.
- **`TableExtension`** — table-level extension point, keyed by table id (e.g.
  `customers.index`); exposes `columns()`, `filters()`, `actions()`, `bulkActions()`, and
  `searchQuery()`. Column/action classes may name a Vue component (resolved from the JS
  registry) or fall back to generic renderers. `TableExtensionResolver` merges every
  extension for a table id, orders the result (see Ordering below), and ships it as Inertia
  props (`columns`, `tableActions`, `tableBulkActions`, `tableFilters`, `tableFilterValues`).
  Index controllers pull columns + row actions + bulk actions + search/filter queries
  through a shared `ResolvesTableExtensions` concern (used by Customers and Channels), so
  table extension is a cross-cutting pattern rather than one wired page. Filters render as
  toolbar dropdowns next to the page's own (an automatic "All" default, options as
  [value => label]); the selection round-trips as a nested `filter[{key}]` query param and
  `applyFilters()` runs each filter's `query()` hook server-side before pagination. Row actions render in a per-row ellipsis
  dropdown; bulk actions render in the selection toolbar shown when rows are checked (acting
  on the selected row ids). First-party Edit/Delete are ordinary `TableAction` entries, so
  first-party and add-on actions share one render path.
- **`PageAction`** — header-level extension point covering both record pages ("Create
  return" on an order, handed the record as `$context`) and listing pages ("Import Products"
  above a table, handed `null`), unified into one abstract keyed by page id. Mirrors
  `TableAction`'s shape (`key`/`label`/`icon`/`url`/`method`/`confirmationMessage`/
  `permission`/`visible`) plus `primary()` to promote an action from the overflow ellipsis
  to an always-visible inline button. Registered via `Section::pageActions()`, resolved by
  `PageActionResolver` (same shape as `TableExtensionResolver`, no new registry class),
  shared as a permission-filtered `pageActions` prop, and rendered by `<PageActions>` in the
  page header.
- **Ordering (`Position` + `OrderResolver`)** — every ordered entry (navigation items, table
  columns, and all action types) exposes `position(): Position`
  (`Lunar\Panel\Support\Position`), defaulting to `priority(50)`. `Position` supports coarse
  `priority(int)` ordering and relative `before(key)`/`after(key)` anchors. A single shared
  `OrderResolver` sorts by priority (stable, ties keep registration order), then repositions
  anchored entries adjacent to their target key; a missing target falls back to priority and
  logs a warning (consistent with the unknown-section-key behaviour above), and circular
  anchors fall back to priority. The navigation registry, table resolver, and action
  resolvers all order through it — one ordering model panel-wide — so an add-on can place
  work precisely (e.g. a row action immediately after first-party `edit`) without guessing
  priority numbers.
- **Routes** — registered on `app booted` under the configured prefix: an unauthenticated
  group loading `routes/auth.php`, and an authenticated group (`Authenticate` middleware
  forcing the panel guard + `HandlePanelInertiaRequests`, root view `lunar-panel::app`)
  loading `routes/web.php` followed by every section's route registrar closures. Add-on
  routes therefore mount inside the panel's authenticated Inertia context.
- **`HandlePanelInertiaRequests`** — shares `auth.user`, `flash`, panel config (name, path,
  URLs), `locale`, `availableLocales`, the resolved permission-filtered `navigation` and
  `settingsNavigation` trees, and the current page's slot entries. (The translations version
  hash travels in the translations endpoint's response rather than as a prop.) A `settings`
  route redirects to the first settings page the user can see, giving add-ons a stable
  settings entry point.

First-party code registers through this same public API — a `SalesSection` (key `sales`,
containing Customers) and a `ChannelsSection` (settings) live in the panel package but are
written exactly as an add-on would write them, so the extension points are exercised from
day one.

### Extension architecture — slots

The one piece the architecture prototype does not already implement, generalised from its
table-extension pattern (and inspired by Statamic's Inertia panel):

- **PHP**: `SlotRegistry` accepts entries of `{zone, component, props, permission,
  priority}` via `$section->slots()` or `Panel::slot()`. `component` is a namespaced JS
  component name (`my-addon::SeoSection`). Zone naming convention:
  `{section}.{page}:{region}[:position]` — e.g. `customers.show:main:after`,
  `customers.index:actions`.
- **Sharing**: the Inertia middleware resolves entries for the current page only,
  permission-filtered, priority-ordered.
- **Vue**: a `<PanelSlot name="customers.show:main:after" v-bind="context" />` component
  renders every registered entry by resolving the component name through
  `window.LunarPanel.resolveExtensionComponent()`, passing slot props from PHP plus page
  context. Unresolvable components are skipped with a console warning.
- Customers and Channels screens ship with a documented set of zones.

### Actions and the shared page scaffold

Actions default into an always-present "more actions" ellipsis dropdown — per table row
(`<RowActions>`) and per page header (`<PageActions>`) — rendered only when it holds at
least one action. This guarantees an add-on can always inject an action into any row or page
without the first-party page having wired a slot for it; promoting an action to an inline
button (`primary()`) is the opt-in exception, reserved for first-party primaries like "Add"
or "Edit". Bulk actions are the one exception: they live in the selection toolbar and act on
the checked row set, not the ellipsis.

That guarantee is structural, not conventional. Every content page renders its header and
body through a shared scaffold — `<PageHeader>` (or `<SettingsShell>` for settings pages) —
that owns the primary buttons, the `<PageActions>` ellipsis, and a standard pair of slot
zones (`{page}:main:before`, `{page}:main:after`) present on every page. A page cannot omit
what it no longer hand-rolls. Three layers, in descending reliability: the scaffold itself
(the actual guarantee); `tests/panel/Unit/PageScaffoldTest.php`, a convention test asserting
every content page (auth/account pages exempt) is built through the scaffold, so a new page
that skips it fails CI; and the panel package's `CLAUDE.md`, which documents the
build-a-page convention for humans and Claude.

### Extension architecture — JS runtime

Ported from the architecture prototype:

- **`window.LunarPanel`** — the global registry add-on bundles talk to: `booting(cb)` (with
  a pre-boot pending stash so bundle/registry load order never matters), `registerPages()`,
  `registerComponents(namespace, comps)` / `resolveExtensionComponent(name)`,
  `registerLayout()`, `registerTranslations()`. A `.d.ts` is published so add-on authors get
  typings.
- **Page resolution** — the Inertia `resolve()` checks the runtime registry first, falls
  back to the panel's local `import.meta.glob` pages, and on hard refresh awaits
  `DOMContentLoaded` (all deferred add-on IIFEs executed) then drains the boot queue and
  retries. This ordering fix is the crux of loading add-on pages without recompilation and
  ports verbatim.
- **Add-on builds** — the panel exports a Vite plugin (`@lunarphp/panel/vite-plugin`)
  forcing IIFE output with `vue`, `@inertiajs/vue3`, and friends externalised to window
  globals, which the panel's own `app.ts` publishes at startup. Add-ons compile once,
  publish prebuilt bundles, and the root Blade view emits a `@vite` tag per registered
  module (own build directory and hot file, so add-on HMR works in dev).
- **Assets** — panel assets publish to `public/vendor/lunar-panel/build`; an add-on serves
  its compiled build from `public/vendor/lunar-panel/{key}` (the `buildDirectory` it passes
  to `PanelManager::vite()`). Every registered `__buildSourcePath` gets a vendor:publish
  mapping automatically (`{key}-panel-assets`, aggregated under `panel-all-assets`) for
  production deployment; `php artisan lunar:panel:link` symlinks it instead for local
  development, and `php artisan lunar:panel:install` publishes config plus all assets.
- **i18n** — vue-i18n (externalised to `window.VueI18n` so add-on pages share the panel's
  instance); PHP lang groups served as JSON per locale from a translations endpoint, cached
  in localStorage keyed by an mtime-derived version hash. Add-on lang namespaces opt in via
  `Section::langNamespaces()` / `Panel::translations()` and are served as
  `{namespace}::{group}` message keys with per-namespace fallback to the app fallback
  locale; add-ons can also push messages at runtime via `registerTranslations()`. Staff pick
  their panel language from the user menu (shared `availableLocales` prop, persisted as
  `staff.preferred_locale`, applied by the `Authenticate` middleware).

### Frontend — design system and components

The visual layer ports from the design prototype and nothing else:

- Vue 3 + TypeScript, Inertia v2, Tailwind CSS v4 (tokens declared in-CSS with `@theme`:
  the OKLCH ink/line/surface/sage/danger/warn scales), Reka UI primitives, Geist + Geist
  Mono, the single-file `Icon.vue` map, 13px base typography, dark mode via a `.dark` class
  with light/dark/system resolution.
- Components ported (and converted to TS) as the scoped screens need them: `Button`,
  `TextInput`, `Textarea`, `Select`, `Combobox`, `Checkbox`, `Toggle`, `Tabs`, `FieldLabel`,
  `DataTable` + `DataTableActions` + `Pagination`, `Dialog`, `ConfirmDialog`, `Slideout`,
  `Toaster`, `TopBar`, `Section`, `SideCard`, `Nav`/`SettingsNav` (desktop sticky sidebar,
  mobile drawer, collapse toggle), `EmptyState`/`PageEmpty`, `AuthLayout`, `CodeInput`,
  `ActivityTimeline`, `AddressCard`, `Badge`.
- A default `PanelLayout` applied to any page that does not declare its own.

### Authentication

Against the core `staff` guard (`Lunar\Core\Models\Staff`), configurable via
`lunar.panel.guard`. Screens use the design prototype's `AuthLayout` and two-step flow.

- **Login** — credentials validated against the guard, rate-limited, session regenerated on
  success; redirect to the intended URL or dashboard. Logout invalidates the session.
- **2FA** — TOTP app authentication using the existing Staff columns
  (`app_authentication_secret`, `app_authentication_recovery_codes`). When enabled for the
  staff member, login becomes two-step: credentials, then a code challenge accepting either
  a TOTP code or a recovery code (recovery codes are single-use and replaced on use).
  Setup lives on an Account/Security page: QR enrolment, code confirmation before
  activation, recovery-code display and regeneration, and disable. Column semantics match
  Filament v5's app-authentication feature so a staff member's 2FA works in both panels
  during the transition. Implementation uses `pragmarx/google2fa` (+ a QR code renderer) —
  a new dependency requiring approval.
- **Password reset** — forgot/reset flow using a password broker for the staff provider.

### Screens in scope

- **Auth** — Login, 2FA challenge, forgot/reset password, Account/Security (2FA setup and
  password change).
- **Dashboard** — minimal placeholder page as the navigation landing target. Widgets are out
  of scope.
- **Customers** (a `sales` section) — index with search, customer-group filter, sort, and
  pagination on `DataTable`; create; detail/edit with personal details (title, names,
  company, tax identifier, account ref), customer-group assignment, and tabs for Saved
  addresses (CRUD), Users (link/unlink to storefront users), Order history (read-only list
  over the core Order model), and Activity (activity log); lifetime purchase stats and an
  editable notes card in the sidebar; delete with confirmation. Order rows do not link
  anywhere until an Orders section exists; dynamic attribute rendering (`attribute_data`)
  is deferred because it pulls in the whole field-type system.
- **Settings layout** — the settings shell with its own grouped, permission-filtered
  sidebar driven by the settings-navigation registry, matching the design prototype's
  `SettingsNav`.
- **Channels** (settings section) — list on `DataTable`; inline create dialog; edit page
  (name, auto-slugged handle, URL, default toggle, status); delete with confirmation,
  blocked when `Channel::hasOrderHistory()` is true or the channel is the default. The
  default flag moves by promoting another channel, never by unsetting, so a store with
  channels always has a default — the core `UpdateChannel`/`DeleteChannel` actions enforce
  both rules and the edit page disables the corresponding controls.

### Data layer and authorization

Thin Inertia controllers with form requests for resource CRUD input (shared between store
and update where the rules are identical); the small single-field auth and account flows
validate inline, mirroring Laravel's own starter-kit convention. Mutations go through
core's action/contract conventions — where a needed operation has no core action yet, the action is added to core
(with contract and `execute()`) per the service-layer rules, not implemented panel-side.
Authorization uses the existing permission manifest and the `Gate::after` admin override;
the same permission keys drive navigation filtering, slot filtering, and route/policy
checks.

### Testing

- New `panel` Pest testsuite in `phpunit.xml` (and the CI matrix): auth including the full
  2FA lifecycle, registries (navigation/slot/table/action resolution, `OrderResolver`
  priority/anchor/missing-target-fallback ordering, permission filtering, section extension
  matching), the `PageScaffoldTest` convention test asserting every content page goes through
  the shared scaffold, Inertia page and prop assertions for Customers and Channels CRUD, and
  a fixture add-on package inside the test suite proving an add-on can register a section
  extension, navigation, slot entries, table columns, row/bulk actions, and page actions
  (including a `before`/`after`-anchored action) without touching panel source.
- Vitest for the JS extension runtime (boot ordering, pending stash, page resolution
  fallbacks, component registry).
- PHPStan and Pint as required by the monorepo pipeline.

### Out of scope

Inventory, Accounting, and Reviews screens (add-ons or prototype-only); the SEO product
section (documented as the canonical slot example instead); Orders, Products, and all other
resources; dashboard widgets; command palette; global search; dynamic attributes/field-type
rendering; SSR; removal of the Filament admin. An add-on **tabs** extension point for detail pages is deliberately not
provided (see Alternatives): an add-on wanting an in-context view registers a route/page and
links to it from a `PageAction`, or injects into a slot zone.

## Alternatives considered

- **Keep building on Filament** — rejected: the direction is a bespoke Vue design system
  with runtime Vue extensibility, which Filament's Livewire architecture cannot host.
- **Plain SPA + REST API** — rejected: Inertia keeps routing, auth, and authorization
  server-side with no duplicated API layer, and the architecture prototype already proves
  the package-context Inertia setup.
- **Bundle-time extension** (add-ons contribute source compiled by the consuming app, as
  Filament/Nova themes do) — rejected: forces a Node toolchain and rebuild onto every
  consumer for every add-on install. The IIFE-plus-shared-globals-plus-runtime-registry
  design is the core differentiator and is already validated.
- **Port the architecture prototype's UI and reskin** — rejected: its shadcn-style
  component system does not map onto the design prototype's Reka-plus-tokens system;
  reskinning costs more than rebuilding screens against ported primitives.
- **Split panel + section packages now** (as the architecture prototype does with
  `catalog`) — rejected for the initial scope: one package with sections registered through
  the public API keeps the dogfooding benefit; splitting later is mechanical.
- **Model page actions as generic slot zones instead of a typed `PageAction`** — rejected: a
  slot is "render this arbitrary component here", right for open-ended content (the SEO
  section) but wrong for something structurally always "a labelled, permission-gated,
  optionally-confirmed link or button". Forcing every add-on to hand-roll a dropdown item as
  a Vue component is the ergonomic regression `TableAction` already avoids for rows.
- **Keep record-header and listing-header actions as two separate concepts** — rejected:
  they are the same shape, differing only in whether the page has a record to pass as
  context. One `PageAction` keyed by page id with an optional `$context` covers both with
  less surface than two abstracts.
- **A `tabs()` extension point letting add-ons register detail-page tabs** — rejected on
  design grounds. Columns, row actions and bulk actions grow a list gracefully; a tab bar is
  a bounded, curated structure where every addition costs shared horizontal space and
  cognitive load (Filament's add-on tab sprawl is the cautionary case). The legitimate need
  — an in-context view of a record — is served by registering a route/page and linking from
  a `PageAction`, or injecting into a slot zone. It can be added later, additively, if real
  demand appears.
- **Invent a parallel `priority`/`before`/`after` ordering triple for actions** — rejected:
  the panel already ships `Position` for columns, so one shared `Position` + `OrderResolver`
  serves navigation, columns and every action type rather than a per-extension-point scheme.

## Migration impact

- **Database migrations**: two — a core `staff_password_reset_tokens` table backing the staff password broker (registered alongside the staff guard), and a core `staff.preferred_locale` column backing the panel's per-staff locale switcher. Auth otherwise uses the existing staff 2FA columns; Customers and Channels use existing core tables.
- **Dependencies**: new require on `inertiajs/inertia-laravel` and `pragmarx/google2fa`
  (+ QR renderer, e.g. `bacon/bacon-qr-code`) in the panel package — needs approval before
  implementation. Frontend deps (Vue, Reka UI, Tailwind v4, vue-i18n) are npm dev-time
  only; consumers receive prebuilt assets.
- **Breaking changes**: the Filament admin's config key moves from `lunar.panel` to
  `lunar.admin` (file rename `config/panel.php` to `config/admin.php`). v2 is unreleased,
  so no published upgrade path is required; the v1 upgrade package is unaffected.
- **Translations**: new lang groups in `packages/panel/resources/lang/` across all 16
  locales from the first slice.
- **Filament / admin impact**: none beyond the config rename; both panels run side by side
  on different route prefixes sharing the staff guard and permission manifest.
- **Public contract surface**: this spec introduces new contract surface — the `Panel`
  facade, `Section`/`SectionExtension` (including the `pageActions()` hook), the registries,
  the `TableAction`/`TableBulkAction`/`PageAction` abstracts and their resolvers,
  `Lunar\Panel\Support\Position` + `OrderResolver`, the Inertia props (`navigation`,
  `settingsNavigation`, `availableLocales`, slot entries,
  `tableColumns`/`tableActions`/`tableBulkActions`, `pageActions`), the
  `Section::langNamespaces()` hook and `{namespace}::{group}` message-key scheme, the
  `{key}-panel-assets`/`panel-all-assets` publish tags, the standard `{page}:main:before`/`{page}:main:after` slot zones,
  `window.LunarPanel`, the `@lunarphp/panel` and `@lunarphp/panel-vite-plugin` npm packages,
  and the slot zone naming scheme. Zone names, registry APIs, and action abstracts are
  treated as contract from first release.

## Open questions

- ~~Exact encoding of `app_authentication_secret` / recovery codes in Filament v5~~ — resolved in slice 3 against filament/filament v5.6.5: the secret is a 16-char base32 string behind Laravel's `encrypted` cast; recovery codes are an `encrypted:array` cast whose elements are bcrypt hashes (8 codes, `Str::random(10).'-'.Str::random(10)`, single-use, replaced on use); TOTP is SHA1/6-digit/30s with a verification window of 8. Covered by `tests/panel/Feature/Auth/FilamentCompatibilityTest.php`.
- ~~Are the panel's prebuilt assets committed to the repo~~ — resolved during slice 3: NOT
  committed. `packages/panel/public/build/` is gitignored; assets are built on demand
  (`npm run build` + `php artisan vendor:publish --tag=panel-assets`) rather than shipped in
  git, consistent with how the package was actually built across all slices.
- Confirm the renamed Filament admin config key (`lunar.admin` proposed). Owner:
  maintainers, spec review.
- 2FA policy: optional per staff member initially; is an enforce-for-all option needed in
  this scope? Default assumption: optional, no enforcement setting. Owner: spec review.
- ~~Monorepo Node tooling~~ — resolved: single `package.json` local to `packages/panel`,
  no hoisted workspace.

## References

- Design prototype: `/Users/glenn/GitHub/lunarphp/lunar-v2-ui` (visual system, screens)
- Architecture prototype: `/Users/glenn/Herd/lunar2/packages/lunar/packages/{panel,catalog,addon_example}`
  — extension registries, `window.LunarPanel`, Vite plugin, worked add-on example
- Staff guard intent: `packages/core/config/staff.php`
- Statamic's Inertia panel extension model (prior art for slots/component registration)
- Related specs: [[0016-service-layer-di]] (action conventions), [[0046-public-id-external-addressing]]
  (external addressing used in panel URLs)

## Implementation plan

- [x] Slice 1 — Package scaffold: composer/provider wiring, `lunar.panel` config (+ Filament
  admin config rename), route skeleton, root Blade view, panel Vite build, asset
  publish/link commands, Tailwind theme tokens, `panel` test suite.
- [x] Slice 2 — PHP extension core: `PanelManager` + facade, `Section`/`SectionExtension`,
  `NavigationRegistry` (+ settings), `SlotRegistry`, `TableExtension` + resolver, Inertia
  shared-props middleware, fixture add-on Pest coverage.
- [x] Slice 3 — Auth: login, logout, rate limiting, password reset, 2FA challenge + setup,
  Account/Security page; `AuthLayout` and form primitives ported.
- [x] Slice 4 — Shell: `PanelLayout`, sidebar/settings nav rendered from shared props,
  dashboard placeholder, dark mode, i18n endpoint + vue-i18n.
- [x] Slice 5 — JS extension runtime: `window.LunarPanel`, page resolution, `PanelSlot`,
  add-on Vite plugin, published `.d.ts`, Vitest coverage.
- [x] Slice 6 — Customers section: index (search/filter/sort/pagination), create,
  detail/edit with addresses/users/activity tabs, delete; slot zones and table extension
  wired and dogfooded; core actions added where missing.
- [x] Slice 7 — Settings layout + Channels section: settings shell, channels list/create/
  edit/delete with order-history guard.
- [x] Slice 8 — Example add-on + extension guide: a minimal reference add-on exercising
  pages, navigation, slots, and table extensions; developer documentation.
- [x] Slice 9 — Ordering primitive: `Lunar\Panel\Support\Position` and the shared
  `OrderResolver` (priority, then `before`/`after` anchors; missing-target fallback with
  warning; circular-anchor guard), unit-tested. Navigation retrofitted to `Position` and its
  sorting routed through `OrderResolver`; `position()` added to table column/action types.
- [x] Slice 10 — Wire `TableAction`/`TableBulkAction` into Customers: resolver calls,
  `tableActions`/`tableBulkActions` props, row actions collapsed into the per-row ellipsis,
  a `BulkActionsToolbar` driven by props, first-party Edit/Delete as registered actions.
  The merged first-party + add-on column and action sets are ordered through `OrderResolver`,
  finally honouring `position()`.
- [x] Slice 11 — Extract the resolve-and-share sequence into the `ResolvesTableExtensions`
  concern and apply it to Channels, proving the table pattern is cross-cutting.
- [x] Slice 12 — `PageAction` abstract (primary tier, optional `$context`),
  `PageActionResolver`, `Section::pageActions()` hook, `pageActions` prop, and a
  `PageActions` header component (primary buttons + always-present overflow ellipsis).
- [x] Slice 13 — Shared page scaffold: `PageHeader`/`SettingsShell` owning the primary
  buttons, `<PageActions>` ellipsis, and standard `{page}:main:before`/`{page}:main:after`
  zones. Every content page refactored onto it; `PageScaffoldTest` convention test added;
  page-building convention documented in the panel package `CLAUDE.md`.
- [x] Slice 14 — Extend the example add-on to register one of each — a row action, a bulk
  action, a record-page action, a listing-page action, and a `:main`-zone slot entry, at
  least one placed with a `before`/`after` anchor — proving every action extension point,
  relative ordering, and panel-wide slot zones end to end.
- [x] Slice 15 — Publish the add-on-facing frontend surface: `@lunarphp/panel` (layout/page
  components, types generated via `build:types`) and `@lunarphp/panel-vite-plugin` as npm
  packages under a monorepo workspace; promote the example add-on to a real
  `packages/panel-addon-example` package (composer autoload, split matrix, self.version).
