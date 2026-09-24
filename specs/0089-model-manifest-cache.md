# 0089: Cached model manifest

- Status: draft
- Author: Alec Ritson
- Created: 2026-09-24
- TODO item: Cached model manifest: build the discovered model list with `php artisan optimize`

## Problem

`Lunar\Core\Manifests\ModelManifest` finds Lunar's models with `spatie/php-structure-discoverer`: `Discover::in($dir)->classes()->extending(Base::class)->get()`. That call walks the directory and parses every PHP file in it to find classes extending `Models\Base`, and it runs on every boot:

- `register()` scans `packages/core/src/Models` (52 models) during `LunarServiceProvider::register()`.
- `morphMap()` needs the same list during `LunarServiceProvider::boot()`.
- `addDirectory()` scans any directory an add-on registers, e.g. `table-rate-shipping`'s `ShippingServiceProvider`.

Measured in a host app (a fresh app per request, classes already loaded, as with a warm OPcache), one scan of the core models directory costs about 21 ms. lunarphp/lunar#2754 makes `register()` and `morphMap()` share one scan, which takes `LunarServiceProvider::boot()` from about 47 ms to about 6 ms. The one remaining scan is still about 21 ms on every request, plus each `addDirectory()` scan, spent finding a list of classes that only changes when code is deployed. On slower shared hosting it is proportionally more (about 40 ms on a host running about 2x slower).

Laravel already solves this problem for its own boot-time discovery (config, routes, events, package providers): discover once at deploy time, write a PHP array under `bootstrap/cache/`, and `require` it on every request. Lunar does the same for add-ons (`Lunar\Core\Addons\Manifest` writes `bootstrap/cache/lunar_addons.php`). The model manifest has no equivalent.

## Proposal

### A cache file

`ModelManifest` reads the discovered classes from `bootstrap/cache/lunar_models.php` when that file exists. The file returns a plain array keyed by directory:

```php
<?php return [
    '/path/to/vendor/lunarphp/core/src/Models' => [
        Lunar\Core\Models\Address::class,
        // ...
    ],
    '/path/to/vendor/lunarphp/table-rate-shipping/src/Models' => [
        // ...
    ],
];
```

`ModelManifest::discover(string $dir)` (added in lunarphp/lunar#2754) looks the directory up in the file first. A directory missing from the file falls back to scanning, as today, so an add-on registered after the cache was built still works. It just isn't cached until the next build.

With no file present, behaviour is exactly what lunarphp/lunar#2754 ships: one live scan per directory per process. That is the normal state in local development, so adding or renaming a model never needs a cache clear there.

The path comes from `$app->bootstrapPath('cache/lunar_models.php')`, the same directory the add-on manifest and Laravel's own caches use.

### Building and clearing it

Two console commands in `packages/core`:

- `lunar:models:cache` resolves the model manifest, scans the core models directory plus every directory registered through `addDirectory()` during boot, and writes the file with `var_export`. This uses the same write-to-temp-then-rename approach as Laravel's `config:cache`, so a request never reads a half-written file.
- `lunar:models:clear` deletes the file.

`LunarServiceProvider` registers both with Laravel's optimize hook:

```php
$this->optimizes(
    optimize: 'lunar:models:cache',
    clear: 'lunar:models:clear',
    key: 'lunar-models',
);
```

So `php artisan optimize`, which deploy scripts already run for config and route caching, builds the file, and `php artisan optimize:clear` removes it. Nobody has to remember an extra deploy step.

### What does not change

- The `ModelManifest` contract, morph keys (`getMorphMapKey()`) and route bindings.
- Extending models, `addDirectory()`, and `lunar.database.morph_prefix`.
- Local development without `optimize`: a live scan, as now.

## Alternatives considered

- **Do nothing beyond lunarphp/lunar#2754.** Removes half the cost. The other ~21 ms per request stays, and it is the largest single item left in `LunarServiceProvider`.
- **Cache through the Laravel cache store**, the way `AttributeCache` does. This keeps the list in Redis or the database, so the copy survives a deploy, and a deploy that adds or moves a model then serves a stale morph map until someone flushes the cache. A stale morph map breaks polymorphic relations rather than just being slow. It also costs a cache round trip on every request, where a `require` of an OPcached file costs almost nothing. The file approach inherits `optimize`'s lifecycle, so it is rebuilt by the same deploy step that changes the code.
- **spatie/php-structure-discoverer's `StructureScout` + `structure-scouts:cache`.** Its default driver is the Laravel cache store (same staleness problem), it only looks for scout classes under `structure-discoverer.structure_scout_directories` (default `app_path()`), so a scout inside `vendor/` is not found unless every host app edits that config, and it does not hook into `optimize`.
- **Hardcode the core model list** instead of discovering it. Removes the scan for core, but every new model then needs a manual list entry, and add-on directories still scan. The cache keeps discovery as the source of truth.
- **Build the file from `lunar:addons:discover`** alongside the add-on manifest. That command isn't wired into composer scripts by default (host apps typically only run `package:discover`), so the file would silently never be built. `optimize` is the step deploys already run.

## Migration impact

- Database migrations: none.
- Breaking changes: none. Two new commands and a new file under `bootstrap/cache/` (already gitignored in a standard Laravel app).
- Upgrade path for v1.x: none needed. Consumers get the benefit by running `php artisan optimize` on deploy.
- Translations: none. Command output is console-only.
- Filament / admin: none.

## Open questions

- **Key the file by directory path, or by a relative/package identifier?** Absolute paths are simplest and match what `addDirectory()` receives. They're correct as long as the file is built on the machine that serves it, which `optimize` guarantees in a normal deploy. Building the cache in CI and shipping it in an artifact to a different path would miss every entry and fall back to scanning. That's correct but uncached. Proposed: absolute paths, documented. Owner: reviewer.
- **Should `lunar:models:cache` fail loudly if a class in the list no longer exists** at load time, or should `ModelManifest` detect a stale file and rescan? Proposed: neither. A stale file only happens when code changes without `optimize` running, which is the same contract as `config:cache`.

## References

- lunarphp/lunar#2754: share one scan between `register()` and `morphMap()` (slice 0 of this work).
- `Lunar\Core\Addons\Manifest`: existing `bootstrap/cache/lunar_addons.php` precedent.
- Laravel `ServiceProvider::optimizes()` (Laravel 11.27+).
- `spatie/php-structure-discoverer` `Discover::withCache()`: measured at 0.03 ms for the cached call against ~21 ms uncached.

## Implementation plan

- [x] Slice 0: one scan per directory per process (lunarphp/lunar#2754).
- [ ] Slice 1: `ModelManifest` reads `bootstrap/cache/lunar_models.php`; `lunar:models:cache` / `lunar:models:clear`; `optimizes()` registration; tests for the cached path, the fallback, and the build/clear round trip.
