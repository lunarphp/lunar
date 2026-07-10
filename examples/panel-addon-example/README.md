# Lunar Panel Addon Example

A minimal, standalone reference add-on for `lunarphp/panel`. It is a
separately-versioned, separately-compiled Composer + npm package proving that
the panel's runtime extension mechanism works end-to-end, without the panel
itself needing to recompile.

## What this proves

- **Page registration** — `Widgets/Index.vue` is registered under the
  namespaced name `example-addon::Widgets/Index` via
  `window.LunarPanel.registerPages()`, and served from a route the add-on
  registers itself.
- **Navigation registration** — the add-on adds its own nav group/item
  pointing at that route.
- **Slot registration** — `InfoBanner.vue` is registered as
  `example-addon::InfoBanner` and injected into the documented
  `customers.show:main:after` zone on the Customers edit page.
- **Table extension registration** — `ExampleTableExtension` adds an extra
  column to the `customers.index` table.
- **IIFE compilation** — `resources/js/addon.ts` compiles to a single IIFE via
  the panel's exported `@lunarphp/panel-vite-plugin`, sharing the panel's own
  Vue instance (`window.Vue`) instead of bundling a second copy.

## Installing into a host app

1. `composer require lunarphp/panel-addon-example` (path-repo it locally while
   developing; this example isn't published).
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
