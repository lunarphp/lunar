# Lunar Panel — temporary reference notes

> **Status: placeholder.** These are working notes captured while the package is
> under active development, kept as a reference for writing the real
> documentation later. Expect drift — verify against the source before trusting
> a detail. See also: `specs/completed/0049-inertia-panel.md` (design rationale),
> `packages/panel/CLAUDE.md` (internal conventions), and
> `packages/panel-addon-example/README.md` (the full, tested extension
> walkthrough).

## What it is

`lunarphp/panel` is Lunar's first-party admin panel: Inertia.js + Vue 3 +
Tailwind v4, served by the host Laravel app with no build step required in the
host. It ships compiled assets and a **runtime extension mechanism** — add-on
packages inject pages, navigation, table columns, filters, actions, and screen
sections without the host app (or the panel) recompiling any JavaScript.

It depends only on `lunarphp/core`. The Filament admin (`lunarphp/admin`) is
untouched and the two coexist; the panel is the intended successor.

Current first-party surface: authentication (login, password reset, app/email
2FA, recovery codes), Dashboard, Customers CRUD, Settings area with Channels,
account security and locale screens, and 16 UI locales.

---

## Installing the panel

### 1. Require the package

```sh
composer require lunarphp/panel
```

The service provider (`Lunar\Panel\PanelServiceProvider`) is auto-discovered.
Core must be installed and migrated first (staff table, permissions, etc.).

### 2. Run the installer

```sh
php artisan lunar:panel:install
```

This is a thin wrapper around two `vendor:publish` calls:

- `--tag=panel-config` — publishes `config/lunar/panel.php` (merged as
  `lunar.panel`).
- `--tag=panel-all-assets --force` — copies the panel's compiled build to
  `public/vendor/lunar-panel/build`, favicons to
  `public/vendor/lunar-panel/favicons`, **and** the compiled build of every
  registered add-on module to `public/vendor/lunar-panel/{key}`.

Re-run the asset publish after every package update — the host serves
pre-compiled assets, so stale builds are the most common "my change isn't
showing" cause. `--tag=panel-assets` publishes only the panel's own build.

### 3. Have a staff account

The panel authenticates against Lunar's **staff guard** — `lunar.staff.guard`
(default `staff`, provider backed by `Lunar\Core\Models\Staff`), which core
registers automatically unless `lunar.staff.register_guard` is disabled. A
`lunar.panel.guard` override exists if the host wants a different guard.

Staff with `admin = true` pass every permission check; otherwise access is
per-permission (`hasPermissionTo()`), resolved through a `Gate::after` hook
against the handles in the access-control manifest (e.g.
`sales:manage-customers`). Navigation items, actions, and routes all key off
the same handles, so what a user can see and what they can reach stay in sync.

### 4. Visit it

The panel lives at `/{lunar.panel.path}` — `https://your-app.test/panel` by
default.

### Local development (this monorepo / path repos)

Instead of re-publishing after every rebuild, symlink the builds (this is how
the host app at the repo root is set up):

- Panel's own build: symlink `packages/lunar/packages/panel/public/build`
  to `public/vendor/lunar-panel/build` (manual `ln -s`; favicons still need a
  one-off publish). Because the symlink also exposes the package's
  `public/build/hot` file, running `npm run dev` in `packages/panel` gives
  full Vite HMR in the host app with no further wiring.
- Add-on builds: `php artisan lunar:panel:link` symlinks every registered
  module's `__buildSourcePath` into `public/vendor/lunar-panel/{key}` so a
  rebuild is picked up without re-publishing.

Frontend toolchain (from `packages/panel`, own dependency tree — `npm install`
there first; the example add-on installs via the npm workspace at the
**monorepo root** instead):

```sh
npm run dev        # Vite dev server + HMR
npm run build      # compile assets + regenerate @lunarphp/panel types
npm test           # vitest
npm run type-check # vue-tsc
```

PHP tests: `vendor/bin/pest --testsuite panel --parallel` from the monorepo
root.

---

## Configuration (`config/lunar/panel.php`)

| Key | Default | Purpose |
| --- | --- | --- |
| `path` | `panel` | URI prefix the panel is served under. |
| `name` | `Lunar` | Panel name (browser title, sidebar header). |
| `guard` | `null` | Auth guard; `null` falls back to `lunar.staff.guard`. |
| `route_middleware` | `['web']` | Base middleware for every panel route group. |
| `storefront_url` | `null` | "View storefront" link in the user menu. |
| `support_url` | docs URL | Support link in the user menu. |
| `menus` | `[]` | Optional top-level menu grouping mapping section keys into named menus. |

## Artisan commands

| Command | Purpose |
| --- | --- |
| `lunar:panel:install` | Publish config + all compiled assets (panel and add-ons). |
| `lunar:panel:link` | Symlink registered add-on builds into `public/` for local dev. |

---

## Using the panel

- **Login** at `/panel/login`. Guests hitting any panel URL are redirected
  there; the intended URL is honoured after login.
- **Two-factor auth**: staff enrol themselves under *Account > Security* —
  authenticator app (TOTP with QR code and recovery codes) or email codes.
  Login becomes a two-step challenge once enrolled.
- **Password reset** is the standard Laravel flow, themed, at
  `/panel/forgot-password`.
- **Language**: staff pick their panel locale from the user menu; it persists
  as `staff.preferred_locale` and applies on next request. Locales offered are
  the 16 the panel ships translations for.
- **Navigation** is permission-filtered: an item only renders for staff holding
  its permission handle. The Settings entry route redirects to the first
  settings page the user is allowed to see.
- **Tables** (Customers, Channels, and any add-on table) share one `DataTable`
  component: keyword search, toolbar filters, sortable columns, pagination,
  row-action ellipsis menus, and bulk actions with row selection when any bulk
  action is registered.
- **Dark mode** and sidebar collapse state are per-user UI preferences,
  toggled from the user menu / sidebar.

---

## Extending the panel

This is the panel's defining feature, and it is documented **in depth, with
tested code**, in `packages/panel-addon-example/README.md` — that package is a
standalone Composer + npm add-on exercised by
`tests/panel/Feature/ExampleAddonTest.php` against the real panel routes. Fork
it as a starter (`composer create-project lunarphp/panel-addon-example
my-addon`). What follows is the map, not the territory.

### The model

Everything hangs off a `Section` (`Lunar\Panel\Sections\Section`) registered in
a service provider's `boot()`:

```php
Panel::section(new MySection);          // your own area of the panel
Panel::extendSection(new MyExtension);  // graft onto a section someone else owns
```

A `SectionExtension` supports the same hooks plus `extends(): string` naming
the target section key. Registration must happen during provider boot —
sections are processed once the app has booted, and late registrations are
ignored (with a logged warning).

### The hooks

| Hook | What it registers |
| --- | --- |
| `navigation(NavigationRegistry)` | Sidebar groups/items (label, icon from the built-in set, route, permission). |
| `settingsNavigation(NavigationRegistry)` | Items in the Settings sidebar, same shape. |
| `routes(): ?Closure` | Routes, run inside the panel's route group (prefix, guard, middleware applied for you). Gate them with `can:` middleware yourself. |
| `slots(SlotRegistry)` | Inject a Vue component into a named zone on a page you don't own — zone names are `{routeNameMinusPanelPrefix}:{region}[:position]`, e.g. `customers.edit:main:after`. |
| `tableExtensions(): array` | `['customers.index' => MyTableExtension::class]` — extra `TableColumn`s, `TableFilter`s, row `TableAction`s, `TableBulkAction`s, and a `searchQuery()` hook on a first-party (or other add-on) table. |
| `pageActions(): array` | `PageAction` classes for a page's header ellipsis, keyed by page id; record pages pass the bound model as `$context`. |
| `widgets(): array` | `Widget` classes for the dashboard. Each names a Vue component (namespaced for add-ons), declares span/icon/label/permission/default visibility, and returns its component's props from `data(DashboardRange $range)` — shipped as a per-widget deferred Inertia prop. Staff reorder/hide/re-add widgets per user. |
| `vite(): ?array` | The add-on's compiled JS module — the panel's blade emits its script tag automatically. |
| `langNamespaces(): array` | Laravel translator namespaces whose lang groups the translations endpoint should serve to vue-i18n as `{namespace}::{group}` keys. |

Every entry in an ordered set (nav items, columns, filters, all action types)
carries a `Position` (`Lunar\Panel\Support\Position`): `priority(int)` for
coarse ordering, `before('key')` / `after('key')` to anchor next to another
entry — first-party or add-on. Missing anchors fall back to priority and log a
warning; nothing throws.

### The frontend side

Add-ons compile to a single IIFE with `@lunarphp/panel-vite-plugin`, which
externalises `vue`, `@inertiajs/vue3`, and `@lunarphp/panel` to globals the
panel publishes at startup — no second copy of anything. At the **top level**
of the bundle (never inside `booting()`, which runs after first render):

```ts
window.LunarPanel.registerPages({ 'my-addon::Widgets/Index': WidgetsIndexPage });
window.LunarPanel.registerComponents('my-addon', { InfoBanner });
```

Add-on pages import the panel's UI kit from `@lunarphp/panel` (`PageHeader`,
`PageZone`, `DataTable`, form inputs, overlays, `Button`, `Icon`, ...) — the
exported surface is defined by `resources/js/ui.ts` and typed via the published
package's `dist/ui.d.ts`. The panel auto-wraps add-on pages in its `PanelLayout`
shell, so a page builds only its own header and content. The runtime also
exposes `registerLayout()`, `registerTranslations()`, and
`resolveExtensionComponent()`; the typed contract is the `LunarPanelRuntime`
interface shipped with `@lunarphp/panel`.

Two npm packages are published per release for add-on authors:
`@lunarphp/panel` (UI components + types) and `@lunarphp/panel-vite-plugin`
(the IIFE build preset). Inside this monorepo the root npm workspace resolves
both to local source.

### The canonical slot walkthrough: an SEO card on the product edit page

The product edit page deliberately ships no SEO section — it is the reference
example of what an add-on injects via a slot (specs 0049/0057). The page
exposes named zones at every meaningful seam:

- `products.edit:main:before` / `products.edit:main:after`
- `products.edit:content:after` — after the Basics/Media/Attributes cluster,
  before the variants block (where a content-adjacent card like SEO belongs)
- `products.edit:variants:after` — after the options/variants cluster
- `products.edit:sidebar:before` / `products.edit:sidebar:after`

(The variant edit page carries `products.variants.edit:main:before|after` and
`:sidebar:after`.) Zones receive the page's record as a prop — `:product` here
— so the injected component can read what it decorates. The example add-on
ships the whole flow (`SeoCard.vue`, registered in `addon.ts`, injected from
`ExampleSection::slots()`):

```php
$registry->add(new Slot(
    zone: 'products.edit:content:after',
    component: 'example-addon::SeoCard',
));
```

A real SEO add-on would persist its fields through its own registered routes;
the example card keeps them local to stay a pure slot demonstration.

### Shipping add-on assets

The add-on registers its compiled build with
`PanelManager::vite('my-addon', [...])` (pass `__buildSourcePath` pointing at
its `build/` dir). The panel then gives it a publish tag automatically:
`vendor:publish --tag=my-addon-panel-assets` in production,
`lunar:panel:link` for a dev symlink. `--tag=panel-all-assets` sweeps
everything at once.

### Non-section escape hatches

`PanelManager` (behind the `Panel` facade) exposes the primitives directly for
code that doesn't fit a section: `extendTable()`, `addPageAction()`,
`registerRoutes()`, `translations()`, `slots()`, `navigation()`, `vite()`.
Sections are just the organised way of calling them.

### Gotchas (hard-won; full list in the example README's Troubleshooting)

- Slot zone prefixes come from the target page's **route name** with `panel.`
  stripped — not from what the page conceptually is. The Customers edit page is
  `customers.edit` (there is no `customers.show`); a wrong prefix fails
  silently.
- Table ids (`customers.index`) are plain strings with no central registry — a
  typo produces no error, just a column that never appears.
- Register pages/components at the bundle's top level, not in `booting()`.
- A `ReferenceError` for `Vue` / `InertiaVue3` / `LunarPanelUI` from an add-on
  bundle means the published panel build is older than the vite plugin the
  add-on compiled against — re-publish the panel assets.

---

## Building first-party pages (panel contributors)

Conventions are enforced and documented in `packages/panel/CLAUDE.md`; the
short version:

- Every content page renders its header through `<PageHeader>` (or
  `<SettingsShell>` for settings pages) so the shared page-action ellipsis and
  extension seams always exist — `tests/panel/Unit/PageScaffoldTest.php`
  enforces this.
- Wrap the main body in `<PageZone region="main" position="before|after" />`
  so add-ons have injection points without the page opting in.
- Row/header actions are `TableAction` / `PageAction` classes on the page's
  `Section` (see `SalesSection`, `ChannelsSection`), never bespoke buttons —
  that's what makes them orderable and injectable.
- New UI strings go in `resources/lang/{locale}/{group}.php` across **all 16
  locales** (English first, mirror the key everywhere).
- Changing what add-ons can import means editing `resources/js/ui.ts` (the
  source of truth for the public surface) and mirroring the export in
  `resources/panel-package/index.js`.

## Architecture crib sheet

- `PanelServiceProvider` — merges config, binds the `PanelManager` singleton,
  registers the permission gate, first-party sections, routes (auth group +
  authenticated group, both under the configured prefix and middleware), and
  add-on asset publishing.
- `PanelManager` — the registry hub: sections, navigation (main + settings),
  slots, table extensions, page actions, route registrars, vite modules, lang
  namespaces. `Panel` facade fronts it.
- `HandlePanelInertiaRequests` — shared Inertia props (panel name, nav,
  current-page slots/actions, auth user, flash), derives the current page id
  from the route name.
- `resources/views/app.blade.php` — the single Blade entry; loads the panel
  bundle from `public/vendor/lunar-panel/build` and loops
  `registeredVites()` to emit every add-on's tags (per-module hot files, so
  one module's dev server can't capture another's).
- `resources/js/app.ts` — boots Inertia/Vue/vue-i18n, publishes the
  `window.LunarPanel` runtime and the `window.Vue` / `window.InertiaVue3` /
  `window.LunarPanelUI` globals, holds first render until DOMContentLoaded so
  add-on bundles register in time.
