# 0049 — Inertia admin panel (`lunarphp/panel`)

- Status: draft
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

Initial scope: authentication (including 2FA), the extension points (slots, actions,
navigation), the settings layout, one CRUD implementation (Customers), and one settings
section (Channels).

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
    Concerns/                 RegistersPanelAssets
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
  sections, section extensions, slots, add-on Vite modules, and plain scripts/styles.
  Resolves the panel guard. Registration happens in add-on service providers' `boot()`;
  processing is deferred to `$app->booted()` so registration order never matters.
- **`Section`** (abstract) — a cohesive admin area. Declares `key()` and optionally
  `label()`, `navigation(NavigationRegistry $nav)`, `settingsNavigation(...)`, `routes()`,
  `tableExtensions()`, `slots(SlotRegistry $slots)`, and `vite()`. Registered via
  `Panel::section(new CustomersSection)`.
- **`SectionExtension`** (abstract) — same optional hooks plus `extends()` returning the
  target section key. Lets an add-on graft navigation, routes, table extensions, and slot
  entries onto an existing section. Unknown section keys log a warning rather than throw.
- **`NavigationRegistry`** — groups, top-level items, and children with priorities. Items
  carry `key`, `label`, `icon`, `route`, `exact`, `permission`, `children`. `toArray($user)`
  filters by `$user->can($permission)`, so the sidebar is permission-aware for free. A
  parallel settings-navigation registry drives the settings sidebar. The panel seeds the
  Dashboard item itself.
- **`TableExtension`** — the "actions" extension point. Keyed by table id (e.g.
  `customers.index`); exposes `columns()`, `filters()`, `actions()`, `bulkActions()`, and
  `searchQuery()`. Column/filter/action classes may name a Vue component (resolved from the
  JS registry) or fall back to generic renderers. A resolver merges all extensions for a
  table id and the result ships to the page as Inertia props.
- **Routes** — registered on `app booted` under the configured prefix: an unauthenticated
  group loading `routes/auth.php`, and an authenticated group (`Authenticate` middleware
  forcing the panel guard + `HandlePanelInertiaRequests`, root view `lunar-panel::app`)
  loading `routes/web.php` followed by every section's route registrar closures. Add-on
  routes therefore mount inside the panel's authenticated Inertia context.
- **`HandlePanelInertiaRequests`** — shares `auth.user`, `flash`, panel config (name, path,
  URLs), `locale`, `availableLocales`, a translations version hash, the resolved
  permission-filtered `navigation` and `settingsNavigation` trees, and the current page's
  slot entries.

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
- **Assets** — panel assets publish to `public/vendor/lunar-panel/build`; add-ons to
  `public/vendor/lunar-panel/{key}` via the `RegistersPanelAssets` trait;
  `php artisan lunar:panel:link` symlinks instead of copying for local development.
- **i18n** — vue-i18n; PHP lang groups served as JSON per locale from a translations
  endpoint, cached in localStorage keyed by an mtime-derived version hash. Add-ons can also
  push messages at runtime via `registerTranslations()`.

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
  addresses (CRUD), Users (link/unlink to storefront users), and Activity (activity log);
  delete with confirmation. The prototype's order-history tab and purchase-stat KPIs are
  deferred until Orders exists; dynamic attribute rendering (`attribute_data`) is deferred
  because it pulls in the whole field-type system. The layout leaves room for both.
- **Settings layout** — the settings shell with its own grouped, permission-filtered
  sidebar driven by the settings-navigation registry, matching the design prototype's
  `SettingsNav`.
- **Channels** (settings section) — list on `DataTable`; inline create dialog; edit page
  (name, auto-slugged handle, URL, default toggle, status); delete with confirmation,
  blocked when `Channel::hasOrderHistory()` is true.

### Data layer and authorization

Thin Inertia controllers with form requests. Mutations go through core's action/contract
conventions — where a needed operation has no core action yet, the action is added to core
(with contract and `execute()`) per the service-layer rules, not implemented panel-side.
Authorization uses the existing permission manifest and the `Gate::after` admin override;
the same permission keys drive navigation filtering, slot filtering, and route/policy
checks.

### Testing

- New `panel` Pest testsuite in `phpunit.xml` (and the CI matrix): auth including the full
  2FA lifecycle, registries (navigation/slot/table resolution, permission filtering,
  section extension matching), Inertia page and prop assertions for Customers and Channels
  CRUD, and a fixture add-on package inside the test suite proving an add-on can register a
  section extension, navigation, slot entries, and table extensions without touching panel
  source.
- Vitest for the JS extension runtime (boot ordering, pending stash, page resolution
  fallbacks, component registry).
- PHPStan and Pint as required by the monorepo pipeline.

### Out of scope

Inventory, Accounting, and Reviews screens (add-ons or prototype-only); the SEO product
section (documented as the canonical slot example instead); Orders, Products, and all other
resources; dashboard widgets; command palette; global search; dynamic attributes/field-type
rendering; SSR; removal of the Filament admin.

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

## Migration impact

- **Database migrations**: none. Auth uses the existing staff 2FA columns; Customers and
  Channels use existing core tables.
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
  facade, `Section`/`SectionExtension`, the registries, `window.LunarPanel`, the Vite
  plugin, and the slot zone names. Zone names and registry APIs are treated as contract
  from first release.

## Open questions

- Exact encoding of `app_authentication_secret` / recovery codes in Filament v5 (encrypted
  casts, hashing of recovery codes) — verify against the installed Filament version before
  implementing 2FA, since cross-panel compatibility is a stated goal. Owner: implementation
  slice 3.
- Are the panel's prebuilt assets committed to the repo (as Filament-style packages do) or
  built in CI at release/split time? Affects contributor workflow. Owner: maintainers,
  before slice 1 merges.
- Confirm the renamed Filament admin config key (`lunar.admin` proposed). Owner:
  maintainers, spec review.
- 2FA policy: optional per staff member initially; is an enforce-for-all option needed in
  this scope? Default assumption: optional, no enforcement setting. Owner: spec review.
- Monorepo Node tooling: single `package.json` at the panel package with its own lockfile,
  or hoisted workspace at the monorepo root? Default assumption: panel-local. Owner:
  slice 1.

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
- [ ] Slice 3 — Auth: login, logout, rate limiting, password reset, 2FA challenge + setup,
  Account/Security page; `AuthLayout` and form primitives ported.
- [ ] Slice 4 — Shell: `PanelLayout`, sidebar/settings nav rendered from shared props,
  dashboard placeholder, dark mode, i18n endpoint + vue-i18n.
- [ ] Slice 5 — JS extension runtime: `window.LunarPanel`, page resolution, `PanelSlot`,
  add-on Vite plugin, published `.d.ts`, Vitest coverage.
- [ ] Slice 6 — Customers section: index (search/filter/sort/pagination), create,
  detail/edit with addresses/users/activity tabs, delete; slot zones and table extension
  wired and dogfooded; core actions added where missing.
- [ ] Slice 7 — Settings layout + Channels section: settings shell, channels list/create/
  edit/delete with order-history guard.
- [ ] Slice 8 — Example add-on + extension guide: a minimal reference add-on exercising
  pages, navigation, slots, and table extensions; developer documentation.
