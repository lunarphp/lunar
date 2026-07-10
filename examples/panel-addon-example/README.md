# Lunar Panel Addon Example

A minimal, standalone reference add-on for `lunarphp/panel`. It is a
separately-versioned, separately-compiled Composer + npm package proving that
the panel's runtime extension mechanism works end-to-end, without the panel
itself needing to recompile.

Every code snippet below is copied verbatim from this package's own source —
read the linked file if you want the full context.

## What this proves

- **Page registration** — `resources/js/pages/Widgets/Index.vue` is
  registered under the namespaced name `example-addon::Widgets/Index` via
  `window.LunarPanel.registerPages()`, and served from a route the add-on
  registers itself.
- **Navigation registration** — the add-on adds its own nav group/item
  pointing at that route.
- **Slot registration** — `InfoBanner.vue` is registered as
  `example-addon::InfoBanner` and injected into the real Customers edit
  page's `customers.edit:main:after` zone.
- **Table extension registration** — `ExampleTableExtension` adds an extra
  column to the real Customers index (`customers.index`).
- **IIFE compilation** — `resources/js/addon.ts` compiles to a single IIFE via
  the panel's exported `@lunarphp/panel-vite-plugin`, sharing the panel's own
  Vue instance (`window.Vue`) instead of bundling a second copy.

`tests/panel/Feature/ExampleAddonTest.php` in the monorepo exercises all of
the above against this package's real, unmodified source — including
registering this add-on's service provider and hitting the *real* Customers
routes (`/panel/customers`, `/panel/customers/{id}/edit`) to prove the
extension points actually integrate, not just that they work in isolation.

## Scaffolding a new add-on package

An add-on is a normal Composer package with a Laravel service provider. This
package's own `composer.json`:

```json
{
    "name": "lunarphp/panel-addon-example",
    "description": "Reference add-on proving lunarphp/panel's runtime extension mechanism: a page, a nav item, a slot, and a table extension registered without recompiling the panel.",
    "license": "MIT",
    "type": "library",
    "autoload": {
        "psr-4": {
            "LunarPanelExample\\": "src/"
        }
    },
    "require": {
        "php": "^8.4",
        "lunarphp/panel": "self.version"
    },
    "extra": {
        "laravel": {
            "providers": [
                "LunarPanelExample\\ExampleAddonServiceProvider"
            ]
        }
    }
}
```

The `extra.laravel.providers` entry lets Laravel's package auto-discovery
register the provider without the host app touching `config/app.php`. The
provider itself (`src/ExampleAddonServiceProvider.php`) registers a `Section`
with the panel and a Vite module:

```php
class ExampleAddonServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Panel::section(new ExampleSection);

        $this->app->make(PanelManager::class)->vite('example-addon', [
            'input' => 'resources/js/addon.ts',
            'hotFile' => null,
            'buildDirectory' => 'vendor/lunar-panel/example-addon',
        ]);
    }
}
```

`PanelManager::vite()` is what makes the panel's `app.blade.php` emit a
`<script>` tag for the add-on's compiled bundle automatically — no panel
changes required for a new add-on to ship its own JS.

## Registering a page and a nav item

Both live on a `Section` subclass (`src/ExampleSection.php`), which extends
`Lunar\Panel\Sections\Section`:

```php
public function navigation(NavigationRegistry $registry): void
{
    $registry->group('example-addon-group', 'Example Add-on');
    $registry->addItem('example-addon-group', new NavigationItem(
        key: 'example-addon',
        label: 'Example Add-on',
        route: 'panel.example-addon.index',
    ));
}

public function routes(): ?Closure
{
    return function (): void {
        Route::get('example-addon', fn () => Inertia::render('example-addon::Widgets/Index', [
            'message' => 'Hello from the example add-on! This page was registered at runtime via window.LunarPanel.registerPages(), not compiled into the panel.',
        ]))->name('panel.example-addon.index');
    };
}
```

`routes()` returns a `Closure` rather than eagerly registering routes,
because `PanelManager` only runs it inside the panel's own route group (so it
picks up the panel's URL prefix, guard, and middleware). The Inertia
component name is namespaced (`example-addon::Widgets/Index`) to match the
key the add-on's JS registers the page under — see
[Compiling the add-on bundle](#compiling-the-add-on-bundle-with-vite) below.

## Registering a slot and the zone-naming convention

Slots let an add-on inject a component into a specific spot on a page it
doesn't own. `src/ExampleSection.php`:

```php
public function slots(SlotRegistry $registry): void
{
    // Demonstrates the slot mechanism on the Customer edit page. The zone prefix must
    // match the page's route name with the "panel." prefix stripped — our route is
    // named panel.customers.edit (there's no separate "show" route), so the zone is
    // "customers.edit", not the spec's illustrative "customers.show".
    $registry->add(new Slot(
        zone: 'customers.edit:main:after',
        component: 'example-addon::InfoBanner',
        props: ['message' => 'This banner was injected by the example add-on via a slot.'],
    ));
}
```

A zone name is `{section}.{page}:{region}[:position]`:

- `{section}.{page}` — the panel route name for the page you're targeting,
  **with the `panel.` prefix stripped**. `HandlePanelInertiaRequests` derives
  the current page's prefix the same way (from `$request->route()->getName()`),
  so the zone only matches if this segment is exactly right.
- `{region}` — a named slot inside that page's Vue template (e.g. `main`).
- `[:position]` — an optional qualifier the page template defines (e.g.
  `before` / `after`).

**This is the gotcha this add-on was built to catch**: it's tempting to guess
a zone name from what the page *does* (a "show" page) rather than what its
*route is actually named*. The real Customers edit route is named
`panel.customers.edit` (there is no separate `panel.customers.show`), so the
correct zone prefix is `customers.edit`, not `customers.show`. A slot
registered against the wrong prefix silently never renders — there's no
error, because `SlotRegistry::forPage()` just won't find a match. If your
slot isn't appearing, this is the first thing to check (see
[Troubleshooting](#troubleshooting)).

## Registering a table extension

A `TableExtension` bundles one or more `TableColumn`s (plus optional filters
and actions) and is registered against a table ID — here, the real Customers
index table:

```php
// src/ExampleSection.php
public function tableExtensions(): array
{
    return ['customers.index' => ExampleTableExtension::class];
}
```

```php
// src/Tables/ExampleTableExtension.php
class ExampleTableExtension extends TableExtension
{
    public function columns(): array
    {
        return [ExampleColumn::class];
    }
}
```

```php
// src/Tables/ExampleColumn.php
class ExampleColumn extends TableColumn
{
    public function key(): string
    {
        return 'id';
    }

    public function header(): string
    {
        return 'ID (Example Add-on)';
    }
}
```

`Lunar\Panel\Http\Controllers\Customers\CustomerIndexController` calls
`PanelManager::resolveExtensions('customers.index')` and merges every
registered column's `key()`/`header()` onto the first-party column list, and
(if a column overrides `query()`) applies that hook to the Eloquent query
before pagination — see `Lunar\Panel\Tables\TableColumn::query()` if your
column needs a computed value (e.g. a `withCount()`).

## Compiling the add-on bundle with Vite

`package.json` and `vite.config.js`:

```json
{
    "name": "@lunarphp/panel-addon-example",
    "private": true,
    "type": "module",
    "scripts": {
        "build": "vite build",
        "dev": "vite"
    },
    "devDependencies": {
        "@lunarphp/panel-vite-plugin": "file:../../packages/panel/resources/package",
        "@vitejs/plugin-vue": "^5.2.0",
        "vite": "^6.0.0",
        "vue": "^3.5.13"
    }
}
```

```js
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import lunarPanelPlugin from '@lunarphp/panel-vite-plugin';

// Compiles resources/js/addon.ts to a single IIFE bundle that shares the
// panel's Vue instance (window.Vue) instead of bundling its own copy.
export default defineConfig({
    plugins: [
        vue(),
        lunarPanelPlugin({ name: 'LunarPanelExampleAddon' }),
    ],
    build: {
        outDir: 'build',
        rollupOptions: {
            input: 'resources/js/addon.ts',
        },
    },
});
```

`@lunarphp/panel-vite-plugin` (the panel's `packages/panel/resources/package/vite-plugin.js`)
forces `output.format: 'iife'` and externalises `vue`, mapping it to the
`window.Vue` global the panel's own `app.ts` publishes at startup. That is
what lets the add-on's bundle call into the panel's Vue runtime instead of
shipping a second copy of Vue.

The add-on's entry point (`resources/js/addon.ts`) registers its page and
slot components:

```ts
import WidgetsIndexPage from './pages/Widgets/Index.vue';
import InfoBannerComponent from './components/InfoBanner.vue';

// window.LunarPanel is published by the panel's app.ts before it mounts. Add-on
// script tags may execute before or after that happens, so wait for `booting()`
// rather than registering immediately.
window.LunarPanel.booting(() => {
    window.LunarPanel.registerPages({
        'example-addon::Widgets/Index': WidgetsIndexPage,
    });

    window.LunarPanel.registerComponents('example-addon', {
        InfoBanner: InfoBannerComponent,
    });
});
```

`window.LunarPanel.booting(callback)` is what makes this ordering-independent:
whether the panel's own `app.ts` or the add-on's compiled script tag executes
first (script tag order in the rendered HTML depends on registration order,
not load order), `booting()` either queues the callback (if the panel hasn't
mounted yet) or runs it immediately (if it has). An add-on never needs to
coordinate `<script>` tag ordering with the panel or with other add-ons.

## Installing into a host app

1. `composer require lunarphp/panel-addon-example` (path-repo it locally
   while developing; this example isn't published).
2. Register `LunarPanelExample\ExampleAddonServiceProvider` (auto-discovered
   via `composer.json`'s `extra.laravel.providers`, or add it manually).
3. `npm install` inside this package, then `npm run build`. This produces a
   compiled IIFE + manifest in `build/`.
4. Copy (or symlink) `build/` to `public/vendor/lunar-panel/example-addon/` in
   the host app — matching the `buildDirectory` passed to
   `PanelManager::vite()` in `ExampleAddonServiceProvider::boot()`.
5. The panel's `app.blade.php` loops `PanelManager::registeredVites()` and
   emits a `<script>`/`<link>` tag for every registered module automatically —
   no panel changes required.

## Testing an add-on against the real panel

The monorepo's `tests/panel/Fixtures/ExampleAddonTestCase.php` shows the
pattern for testing an add-on against the panel's real Testbench harness:
register the add-on's own service provider in `getPackageProviders()`, and
add its Inertia page directory to `inertia.testing.page_paths` so
`assertInertia()` can resolve its components. `tests/panel/Feature/ExampleAddonTest.php`
then hits both the add-on's own route (`/panel/example-addon`) and the real
Customers routes (`/panel/customers`, `/panel/customers/{id}/edit`) to prove
the table extension column and slot entry actually appear on the first-party
pages, not only in an isolated fixture.

## Troubleshooting

**A registered page/slot/column doesn't appear.**

- **Slot never renders**: check the zone prefix (`{section}.{page}` before
  the first `:`) against the *route name* of the page you're targeting, with
  `panel.` stripped — not a guessed name based on what the page does. This
  session's own cautionary example: the Customers edit page's route is named
  `panel.customers.edit`, so the correct zone prefix is `customers.edit` —
  registering against `customers.show` (a name that doesn't exist as a route)
  causes the slot to silently never match, with no error anywhere.
- **Page component "not found" client-side**: confirm the Inertia component
  name passed to `Inertia::render()` in your route matches the key used in
  `window.LunarPanel.registerPages({ 'namespace::Path': Component })` exactly,
  including the `namespace::` prefix.
- **Table extension column missing**: confirm the table ID string passed to
  `Section::tableExtensions()` (e.g. `'customers.index'`) matches the ID the
  controller passes to `PanelManager::resolveExtensions()` — these are plain
  strings with no central registry, so a typo produces no error, just a
  column that never appears.
- **Add-on JS never runs**: check the compiled bundle is actually being
  served at the `buildDirectory` path passed to `PanelManager::vite()`, and
  that `window.LunarPanel.booting()` (not a bare top-level call) wraps your
  registration calls.

## Files

- `src/ExampleAddonServiceProvider.php` — registers the section and the Vite
  module.
- `src/ExampleSection.php` — the `Section` implementation (key, navigation,
  routes, slots, table extensions).
- `src/Tables/ExampleColumn.php` / `ExampleTableExtension.php` — the
  `customers.index` table extension.
- `resources/js/addon.ts` — the IIFE entry point.
- `resources/js/pages/Widgets/Index.vue`, `resources/js/components/InfoBanner.vue`
  — the example page and slot component.
