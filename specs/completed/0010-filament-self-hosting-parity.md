# 0010 — Publishable admin resources (and Staff to core)

- Status: accepted
- Author: Glenn Jacobs
- Created: 2026-05-23
- TODO item: "Filament self-hosting parity — devs can drop `lunarphp/admin` and run Lunar inside their own Filament panel, and Staff moves to core"

## Problem

[[0006-filament-bridge-package]] extracted `lunarphp/filament` so the reusable Filament-side primitives could live independently of the turnkey admin shell. The flight plan retires `lunarphp/admin` in v3; v2 is the migration window. Two related problems remain:

1. **No migration path for resources/pages.** A consumer who wants to keep using Filament after the v3 sunset must rebuild every resource, page, and cluster from scratch. The bridge ships the building blocks (`{Model}Form`, `{Model}Table`, `{Model}Infolist`, selectors, actions, global search) but not the assembly. Hand-rolling 21 resources + ~70 pages is not a serious migration story.
2. **Staff is pinned to the wrong package.** `Staff`, its migrations, factory, lang files, Spatie permission integration, and the auth `Manifest` all live in `packages/admin`. `Staff` is a commerce-domain concept (the user accounts that operate the shop), not a Filament concept. Both the existing Filament panel and any future non-Filament panel (Inertia, headless ops console, etc.) need staff accounts; keeping them in admin forces every other panel to depend on Filament for an auth concern.

The naive fix for (1) — move resources into `lunarphp/filament` — replaces "admin owns the resources" with "the bridge owns the resources," which defeats the point. The bridge becomes a turnkey admin under a different name and inherits the same maintenance burden we're trying to walk away from.

## Proposal

Two changes, shipped together as the closing v2 Filament spec.

### A. `Staff` moves to `lunarphp/core`

`Staff` is a commerce-domain concept; it moves to core; both panels depend on it.

- Move `Lunar\Admin\Models\Staff` → `Lunar\Core\Models\Staff`.
- Move `packages/admin/database/migrations/2026_01_02_00000{0,2,3,4,5}_*staff*` → `packages/core/database/migrations/`, collapsed into a single `create_staff_table` baseline migration that already includes the renamed name columns and the app-authentication columns (consistent with [[0003-flatten-migrations]]).
- Move `packages/admin/database/migrations/2026_01_02_000001_create_permission_tables.php` → `packages/core/database/migrations/` (renamed to fit the core ordering).
- Move `Lunar\Admin\Database\Factories\StaffFactory` → `Lunar\Core\Database\Factories\StaffFactory`.
- Move `Lunar\Admin\Auth\Manifest` → `Lunar\Core\Auth\Manifest`, along with the `Permission` / `Role` DTOs it depends on. Replace the `LunarPanel::getPanel()->getAuthGuard()` call with a configurable `staff` guard name resolved from `config('lunar.staff.guard')`.
- Move `Lunar\Admin\Support\Facades\LunarAccessControl` → `Lunar\Core\Support\Facades\LunarAccessControl`.
- Move the `auth.php` and `staff.php` translation keys from `packages/admin/resources/lang/{locale}/` → `packages/core/resources/lang/{locale}/` for all 16 locales. Translation namespace changes from `lunarpanel::` to `lunar::` for these two files only; admin's other lang files keep the `lunarpanel::` namespace.
- Add `spatie/laravel-permission` to `packages/core/composer.json` as a hard dependency; remove it from `packages/admin/composer.json`. Both panels need permission mapping.
- Ship `packages/core/config/staff.php` with the staff guard name, provider name, model class, and a `register_guard` flag. Core's service provider registers the guard and provider automatically on boot when `register_guard` is true.
- Filament-specific contract implementations on the model (`FilamentUser`, `HasName`, `HasAppAuthentication{Recovery}`) **do not move** with the model. The core `Staff` is panel-agnostic. The bridge ships a `Lunar\Filament\Models\Staff` subclass that extends `Lunar\Core\Models\Staff`, implements the four Filament contracts, and uses a `HasFilamentStaffSupport` trait for the `InteractsWithAppAuthentication{Recovery}` plumbing + `canAccessPanel` / `getFilamentName`. The bridge's service provider sets `config('lunar.staff.model')` to its subclass at boot when Filament is installed, so the auth provider resolves the right class without consumers configuring anything.

`lunarphp/admin` keeps `Lunar\Admin\Filament\Resources\StaffResource` (UI), but the model and infrastructure leave.

### B. Resources, pages, and clusters stay in `lunarphp/admin` — and become publishable

Resources, pages, and clusters do **not** move to `lunarphp/filament`. They stay in admin, where they already live, with the existing `Lunar\Admin\Filament\Resources\…` namespace. **Admin becomes the publishable layer**: consumers who want to own a resource publish it into their own app's namespace, then either keep using admin's copy (still works) or exclude admin's copy from registration so the published one wins.

- No file moves out of `packages/admin/src/Filament/`. Resources, pages, clusters, the resource sub-navigation, the dashboard — all stay where they are.
- The `lunarphp/filament` bridge stays as building blocks only: schemas, tables, infolists, components, attribute field types, synthesizers, widgets, selectors ([[0008-filament-entity-selectors]]), actions and global-search descriptors ([[0009-filament-actions-and-global-search]]). No resources, no pages, no clusters.

### C. `lunar:admin:publish` command

A new artisan command that copies a resource (and its full `Pages/` subtree) into the consumer's namespace and rewrites internal references.

```bash
php artisan lunar:admin:publish products
php artisan lunar:admin:publish products orders customers
php artisan lunar:admin:publish --all
php artisan lunar:admin:publish products --namespace="App\\Filament\\Resources" --path="app/Filament/Resources"
```

What it does, per resource:

1. Copies `packages/admin/src/Filament/Resources/{Model}Resource.php` and the entire `packages/admin/src/Filament/Resources/{Model}Resource/` directory into the target path (defaults to `app/Filament/Resources/`).
2. Rewrites the namespace declarations from `Lunar\Admin\Filament\Resources\{Model}Resource…` → `{target-namespace}\{Model}Resource…` across the copied files.
3. Rewrites `use` statements that point at sibling pages/components in the same resource subtree to the new namespace; external references (`Lunar\Filament\Schemas\…`, `Lunar\Admin\Support\Resources\BaseResource`, `Lunar\Core\Models\…`) stay untouched — those are still provided by the installed packages.
4. Prints a one-line "register on your panel" hint with the full class name.

Available resource keys: every entity admin currently ships (`products`, `orders`, `customers`, `collections`, `discounts`, `brands`, `channels`, `currencies`, `languages`, `tags`, `attributes`, `attribute-groups`, `collection-groups`, `customer-groups`, `product-options`, `product-types`, `product-variants`, `staff`, `tax-classes`, `tax-zones`, `roles`).

The command is intentionally **per-resource granular** — consumers publish only the resources they want to own and leave the rest registered from admin. Publishing one resource does not commit the consumer to publishing all of them.

### D. `LunarPanelManager::excludeResources()` for opt-out

Once a consumer publishes `ProductResource`, both the published copy and admin's copy would register against the panel. They need to disable admin's copy.

```php
LunarPanel::panel(fn ($panel) => $panel)
    ->excludeResources([
        \Lunar\Admin\Filament\Resources\ProductResource::class,
    ]);
```

`LunarPanelManager` filters its `$resources` array against `excludeResources()` before passing to the panel. The consumer registers their published `App\Filament\Resources\ProductResource` separately on the panel (`$panel->resources([…])`) — that's the existing Filament v5 pattern, no Lunar-specific wiring needed.

### E. `LunarPlugin` object for what's *actually* in the bridge

The bridge gains a Filament v5 plugin object covering the bits that genuinely belong to it.

```php
use Filament\Panel;
use Lunar\Filament\LunarPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugin(
            LunarPlugin::make()
                ->widgets()          // dashboard widgets
                ->globalSearch()     // descriptors from spec 0009
                ->actions()          // first-party actions from spec 0009
                ->livewireComponents() // bridge Livewire components
        );
}
```

- `LunarPlugin implements Filament\Contracts\Plugin`.
- Each method is independently chainable and disable-able (`->widgets(false)`).
- `LunarPlugin::make()->fullPreset()` enables every bridge feature — used by `lunarphp/admin` to wire the bridge into its panel.
- **No `resources()`, no `policies()`, no `navigation()`, no `make-resource` command.** Resources are not the bridge's concern.
- The `LunarFilamentServiceProvider` keeps registering things that are *not* panel-scoped — synthesizers, view namespaces, lang, blade components. Panel-scoped features (widgets, search, actions, Livewire components) move behind the plugin.

### Migration parity criterion

A consumer on `lunarphp/admin` today can:

1. Decide which resources they want to own — say, `products` and `orders`.
2. `php artisan lunar:admin:publish products orders` → resources land in `app/Filament/Resources/`.
3. Register the published resources on their panel; call `LunarPanel::excludeResources([…])` to stop admin from also registering them.
4. Optionally repeat per resource, on their own timeline, until they own every resource they care about.
5. When v3 lands and `lunarphp/admin` is sunset, drop the dependency. The published resources keep working because they depend on `lunarphp/filament` (building blocks) and `lunarphp/core` (models/auth) — both of which survive v3.

From there, customisation is no longer a Lunar concern — the published code is the consumer's.

## Alternatives considered

- **Move resources into `lunarphp/filament`.** Original direction of this spec; rejected. Reproduces admin's surface area inside the bridge, keeps Lunar on the hook for maintaining it, and locks consumers into our resource composition. Doesn't actually solve the "after v3, what do I own?" question.
- **Generate resources via a `make-resource` scaffolder.** Considered (and was part of the original spec). Inferior to publishing: a scaffolder produces stubs that subclass our resource, so consumers still depend on our internal class hierarchy. Publishing gives them the source.
- **Ship importer/exporter (IDEAS #3) next instead.** Useful, but does not unblock the v3 migration. Sliding behind this.
- **Leave Staff in `lunarphp/admin`.** Forces non-Filament panels to depend on a Filament-shaped package or re-introduce their own Staff model. Both are worse than rehoming once, now.
- **Do nothing until v3.** Defers the rewrite to every consumer at the v3 cutover. Defeats the purpose of giving v2 a transition window.

## Migration impact

### Database

- One new core migration: `create_staff_table` with name columns and app-authentication columns inline.
- One renamed core migration: the existing `create_permission_tables` moves from admin to core under a core-ordered filename.
- The five admin staff migrations and the admin permission-tables migration are deleted from `packages/admin/database/migrations/`. The upgrade package rewrites the `migrations` ledger entries from the old paths to the new core paths.

### Public contract

- `Lunar\Admin\Models\Staff` → `Lunar\Core\Models\Staff` (Rector rule).
- `Lunar\Admin\Auth\Manifest` → `Lunar\Core\Auth\Manifest` (Rector rule).
- `Lunar\Admin\Database\Factories\StaffFactory` → `Lunar\Core\Database\Factories\StaffFactory` (Rector rule).
- `Lunar\Admin\Database\State\EnsureBaseRolesAndPermissions` → `Lunar\Core\Database\State\EnsureBaseRolesAndPermissions` (Rector rule).
- `Lunar\Admin\Support\DataTransferObjects\{Permission,Role}` → `Lunar\Core\Support\DataTransferObjects\{Permission,Role}` (Rector rule).
- `Lunar\Admin\Support\Facades\LunarAccessControl` → `Lunar\Core\Support\Facades\LunarAccessControl` (Rector rule).
- **No resource/page namespace changes.** `Lunar\Admin\Filament\Resources\…` is unchanged.
- `Lunar\Admin\Support\Facades\LunarPanel` continues to point at `LunarPanelManager`; gains an `excludeResources()` method (additive — no breaking change).

### Upgrade package

Rector rules:

1. `Lunar\Admin\Models\Staff` → `Lunar\Core\Models\Staff`
2. `Lunar\Admin\Auth\Manifest` → `Lunar\Core\Auth\Manifest`
3. `Lunar\Admin\Database\Factories\StaffFactory` → `Lunar\Core\Database\Factories\StaffFactory`
4. `Lunar\Admin\Database\State\EnsureBaseRolesAndPermissions` → `Lunar\Core\Database\State\EnsureBaseRolesAndPermissions`
5. `Lunar\Admin\Support\DataTransferObjects\Permission` → `Lunar\Core\Support\DataTransferObjects\Permission`
6. `Lunar\Admin\Support\DataTransferObjects\Role` → `Lunar\Core\Support\DataTransferObjects\Role`
7. `Lunar\Admin\Support\Facades\LunarAccessControl` → `Lunar\Core\Support\Facades\LunarAccessControl`
8. Rewrite `migrations` ledger entries for the five staff migration filenames and the permission-tables migration filename to the new core filenames.

### Translations (16 locales)

- `auth.php` and `staff.php` move from `packages/admin/resources/lang/{locale}/` to `packages/core/resources/lang/{locale}/` for all 16 locales (ar, bg, de, en, es, fa, fr, hr, hu, mn, nl, pl, pt_BR, ro, tr, vi). Keys unchanged.
- Translation namespace for these two files changes from `lunarpanel::` to `lunar::`. The three admin source files that reference these keys are updated in-place; consumers using these keys in templates get a Rector rule to rewrite call sites.
- No new translation keys introduced by this spec.

### Filament / admin

- Admin retains its full resource/page/cluster surface; no behaviour change for consumers using `LunarPanelProvider`.
- Admin gains a new artisan command (`lunar:admin:publish`) and `LunarPanel::excludeResources()`.
- Bridge gains the `LunarPlugin` object covering widgets, global search, actions, Livewire components — the things the bridge already ships. Existing `LunarFilamentServiceProvider` registrations that are panel-scoped move behind the plugin; non-panel-scoped registrations (synthesizers, lang, views) stay in the service provider.
- Bridge gains `Lunar\Filament\Models\Staff` (subclass of core Staff with Filament contracts) and `Lunar\Filament\Concerns\HasFilamentStaffSupport` trait. Bridge overrides `lunar.staff.model` to this subclass at boot.
- The bridge's `Lunar\Filament\Schemas\Staff\StaffForm` and `Lunar\Filament\Tables\Staff\StaffTable` are unchanged — they already consume core's Staff.

## Open questions

- **Auto-registering published resources.** The publish command currently prints a "register this on your panel" hint. Should it also patch the consumer's panel provider automatically? Auto-patching is fragile (we don't know which panel, which provider file, whether the consumer uses a closure or a manager). Resolve before `accepted`. Leaning toward: no, keep the hint. Document the registration step in the published file's class docblock.
- **Path/namespace defaults for `lunar:admin:publish`.** Default to `app/Filament/Resources/` with `App\Filament\Resources` namespace, matching Filament's `make:filament-resource` convention. Confirm before `accepted`.
- **`vendor:publish` tags alongside the command.** Standard Laravel tags (`lunar-admin.resources`, `lunar-admin.products`, etc.) give consumers an alternative path that doesn't go through our command — they copy raw files and rewrite namespaces themselves. Worth shipping for parity with Laravel conventions, or do we want consumers funnelled through the artisan command (so we control the namespace rewrite)? Resolve before `accepted`. Leaning toward command-only — verbatim file copies without namespace rewrite are a footgun.

## References

- [[0006-filament-bridge-package]] — bridge extraction, established the two-package split.
- [[0007-pages-refactor]] — page extension hooks that downstream resource subclasses rely on.
- [[0008-filament-entity-selectors]] — selectors that resources (admin or published) consume.
- [[0009-filament-actions-and-global-search]] — actions + global-search descriptors registered via the new `LunarPlugin`.
- [`packages/filament/IDEAS.md`](../packages/filament/IDEAS.md) — item #1 (Plugin object) is promoted by this spec. Items #11 (standalone pages), #12 (default policies), #16 (make-resource command) are dropped — superseded by the publishable-admin approach.
- [Flight plan](https://docs.lunarphp.com/logs/flight-plan) — v3 sunset of the existing admin shell.
