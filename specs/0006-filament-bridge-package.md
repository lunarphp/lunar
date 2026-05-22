# 0006 — Extract `lunarphp/filament` bridge package

- Status: proposed
- Author: Glenn Jacobs
- Created: 2026-05-22
- TODO item: "Move core Filament e-commerce components to a new `lunarphp/filament` package"

## Problem

The admin panel ([`lunarphp/lunar`](../packages/admin/composer.json)) is a single monolithic Composer package that bundles two distinct things:

1. **The Lunar admin shell** — a turnkey Filament panel: `LunarPanelManager`, `LunarPanelProvider`, navigation, branding, auth, the Dashboard page, cluster pages, the full set of `{Model}Resource.php` thin entries, admin-only Livewire components.
2. **The reusable Filament building blocks** — generic form components, table columns, infolist components, attribute field types, Livewire synthesizers, dashboard widgets, and (post-[[0005-filament-v5-schemas-refactor]]) the per-resource `{Resource}Form` / `{Resource}Table` / `{Resource}Infolist` schema classes.

The reusable layer is the interesting one for downstream developers. Today a developer who wants a Filament admin tailored to their own brand has two options: take Lunar's whole panel and override pieces of it via `LunarPanel::extensions([…])`, or rebuild the equivalents from scratch. There is no path to "use my own Filament panel, but compose Lunar's product form / order table / dashboard widgets into it" — because those components are gated behind the `lunarphp/lunar` install and a Lunar-shaped panel boot.

That coupling also pins the admin shell to the Filament release cadence. When Filament cuts a new major (v5 → v6), Lunar has to ship a coordinated release across core, admin, and every other package even though only the Filament-touching code needs to move.

The [flight plan](https://docs.lunarphp.com/logs/flight-plan) declares the long-term direction: Lunar provides the commerce primitives; the framework you build your admin in stays under your control. By v3 the existing admin shell phases out, but the bridge package stays. v2 is the window where the bridge is born alongside the existing admin so consumers can migrate without forced rewrites.

## Proposal

A new first-party package, `lunarphp/filament`, at `packages/filament/` in this monorepo. It owns every Filament-side primitive that has value outside the Lunar admin shell. The existing admin package keeps the shell pieces and depends on the new bridge for its components.

### Package layout

```
packages/filament/
    composer.json              # lunarphp/filament
    config/                    # publishable config (component defaults, attribute field type registry)
    resources/
        lang/                  # 16 locales — keys for everything moved from admin
        views/                 # Blade partials backing custom components/widgets
    src/
        Forms/Components/      # Attributes, AttributeSelector, MediaSelect, PermissionSelector,
                               #   Tags, TextInputSelectAffix, Translated*, Vimeo, YouTube
        Tables/
            Columns/           # ThumbnailImageColumn, TranslatedTextColumn
            Components/        # KeyValue
            Actions/           # Collections/*
        Infolists/Components/  # Livewire, Tags, Timeline, Transaction
        FieldTypes/            # BaseFieldType + Dropdown, File, ListField, Number, TextField,
                               #   Toggle, TranslatedText, Vimeo, YouTube
        Synthesizers/          # Price + per-field-type Livewire synths
        Widgets/               # Dashboard widgets (Orders/*, Products/VariantSwitcherTable)
        Schemas/               # {Model}Form, {Model}Infolist — one subdir per model
        Tables/{Model}Table.php
        Support/
            Forms/AttributeData.php
            ComponentExtensions/   # registry + facade for stackable extensions (moved from admin)
        LunarFilamentServiceProvider.php
    tests/                     # Pest, Orchestra Testbench, exercises components in isolation
```

Namespace: `Lunar\Filament\…`. Picked because it mirrors how the admin currently namespaces these (`Lunar\Admin\…`) and reads naturally at the call site (`Lunar\Filament\Schemas\Brand\BrandForm`).

### What moves

From `packages/admin/src/` to `packages/filament/src/`:

- `Support/Forms/Components/*` → `Forms/Components/*`
- `Support/Tables/Columns/*` → `Tables/Columns/*`
- `Support/Tables/Components/*` → `Tables/Components/*`
- `Support/Tables/Actions/*` → `Tables/Actions/*`
- `Support/Infolists/Components/*` → `Infolists/Components/*`
- `Support/FieldTypes/*` → `FieldTypes/*`
- `Support/Synthesizers/*` → `Synthesizers/*`
- `Support/Forms/AttributeData.php` → `Support/Forms/AttributeData.php`
- `Filament/Widgets/Dashboard/*` → `Widgets/Dashboard/*`
- `Filament/Widgets/Products/*` → `Widgets/Products/*`
- For every resource under `Filament/Resources/{Resource}/`:
  - `Schemas/{Resource}Form.php` → `packages/filament/src/Schemas/{Model}/{Model}Form.php`
  - `Schemas/{Resource}Infolist.php` → `packages/filament/src/Schemas/{Model}/{Model}Infolist.php` (where present)
  - `Tables/{Resource}Table.php` → `packages/filament/src/Tables/{Model}/{Model}Table.php`
  - `RelationManagers/*.php` → `packages/filament/src/RelationManagers/{Model}/{Name}.php`

The schema/table/relation-manager classes carry their `public static` granular helpers (`getNameFormComponent`, `getSkuTableColumn`, etc.) with them — those helpers are exactly the surface a downstream Filament developer would want when assembling their own resources.

**Publishable stubs.** The schema, table, infolist, and relation-manager classes are also exposed as a publishable group via `php artisan vendor:publish --tag=lunar-filament.schemas`. Publishing copies the classes into the consuming app's `app/Filament/…` tree (or a configurable path), giving developers a one-shot way to take ownership of a Lunar form/table and customise it freely without an extension-hook indirection. Runtime resolution prefers the published copy when present, falling back to the bridge's class otherwise. Publishing is a one-way door — once a class is published, future bridge improvements to that file no longer reach the consumer until they re-merge by hand. Document it as the "I want to own this" escape hatch, distinct from extension hooks (additive) and subclass-and-rebind (full replacement, no copy).

The 17 relation managers currently under `packages/admin/src/Filament/Resources/{Resource}/RelationManagers/` all move — every one is a commerce-model relation (Customer addresses/orders/user, Discount limitations/conditions/rewards, Product customer-group pricing, ProductOption values, AttributeGroup attributes, TaxRate amounts). None are tied to admin-shell UX. Future relation managers added in the admin shell that are genuinely shell-flavoured stay in admin by default; the bridge cut is "is this useful in a non-Lunar Filament panel?".

### What stays in `lunarphp/lunar` (admin shell)

- `LunarPanelManager`, `LunarPanelProvider`, `Auth/`, `Models/Staff`, `Console/Commands/MakeLunarAdminCommand`.
- `Filament/Resources/{Resource}.php` — thin entries with `getModel`, navigation, page registration. The `form()` / `table()` / `infolist()` methods become one-line delegates to the bridge classes: `return \Lunar\Filament\Schemas\Brand\BrandForm::configure($schema);`.
- `Filament/Resources/{Resource}/Pages/*` — page classes are tied to the Lunar admin panel's UX and stay.
- `Filament/Pages/Dashboard.php`, `Filament/Clusters/Taxes.php`, `Filament/AvatarProviders/*`.
- `Support/Resources/BaseResource.php` + concerns (`HasLunarPermissions`, `ResolvesModelContract`, `HasScoutGlobalSearch`).
- `Support/Pages/*` — admin page base classes and concerns.
- `Livewire/*` — admin-specific Livewire components.

### Extension hook system

[[0005-filament-v5-schemas-refactor]] established `LunarPanel::extensions([{Resource}Form::class => […]])` as the registration surface, with stackable extensions targeting the split-class form/table/infolist classes. Those target classes now live in the bridge package, but `LunarPanel` lives in admin. Two changes:

1. **Hook registry moves to the bridge.** A new facade `LunarFilament::extensions([…])` owns the registry of stackable hooks against bridge schema/table classes. The bridge classes call into it from their `configure()` methods.
2. **`LunarPanel::extensions([…])` becomes a thin proxy.** It forwards to `LunarFilament::extensions([…])` so existing admin callers keep working without a code change. Documented as the canonical entry point for users of the Lunar admin shell; downstream developers building their own panel use `LunarFilament::extensions([…])` directly.

Both facades remain in v2. The proxy on `LunarPanel` is marked for removal in v3 when the admin shell phases out.

### Composer and monorepo wiring

- `packages/filament/composer.json` — `lunarphp/filament`, requires `lunarphp/core: self.version`, `filament/filament: ^5.0`, plus the Filament plugin packages the moved code uses (`filament/spatie-laravel-media-library-plugin`, `leandrocfe/filament-apex-charts`, `awcodes/shout`, `awcodes/filament-badgeable-column`, `technikermathe/blade-lucide-icons`).
- `packages/admin/composer.json` — adds `lunarphp/filament: self.version` to its requires; drops the Filament plugin requires that now belong to the bridge (admin keeps Filament itself plus `spatie/laravel-permission`, `barryvdh/laravel-dompdf` since those are admin-shell-only).
- `monorepo-builder.php` — no changes needed; `Option::PACKAGE_DIRECTORIES` already scans `packages/*`.
- `packages/lunar/composer.json` (meta) — adds `lunarphp/filament: self.version` to the umbrella requires.
- Tests: a new `tests/Filament/` testsuite in `packages/lunar/phpunit.xml`, mirroring the other per-package suites. Add Pest unit tests that exercise components and widgets in isolation against Orchestra Testbench (no admin panel boot required) — this is the proof that the bridge is genuinely standalone.

### Versioning and eventual repo split

The bridge package follows Filament's release cadence independently of `lunarphp/core` / `lunarphp/lunar`. Concretely:

- `lunarphp/filament` cuts a new minor when Filament cuts a new minor it tracks.
- A Filament major (v5 → v6) drives a major of the bridge package; the admin shell bumps its constraint when ready.
- The bridge keeps its own `CHANGELOG.md` under `packages/filament/`.

The package is **developed in this monorepo for v2**, then **extracted into its own repository** (`lunarphp/filament`) once the surface stabilises. The monorepo is a staging ground that keeps the bridge in lockstep with the admin shell while the API churns; the eventual split is what unlocks the independent release cadence in practice.

Implications for the monorepo period:

- Build the bridge as if it were already a standalone package — no reaching into sibling-package internals beyond `lunarphp/core`'s public contract surface. No `Lunar\Admin\…` imports inside `packages/filament/src/`. The admin package depends on the bridge, never the other way around.
- Tests live under `packages/filament/tests/` (mirroring the cut layout), exercised via the monorepo's `phpunit.xml` for now but trivially re-rootable when the split happens.
- Documentation, contribution guidelines, and `README.md` are authored as if the package were already in its own repo.
- Monorepo-builder's `SetCurrentMutualDependenciesReleaseWorker` aligns every sub-package to a single shared version during the monorepo period; the bridge wears the shared version until the split. Independent versioning starts at the split, not before — this avoids the need for a custom release worker.

Split trigger: **when stable v2 ships.** The repo extraction is part of cutting v2.0.0 — not gated on API churn metrics or Filament's release pressure, simply the natural cutover point once the bridge has settled into v2's shape. Pre-release v2.0.0-rc / v2.0.0-beta tags stay in the monorepo; the first stable tag is what triggers the move.

Split mechanics (decided closer to the cutover):

- Likely path: `git filter-repo` on `packages/filament/` to a fresh repo with preserved history, point Packagist at the new repo, mark the monorepo entry as a mirror (or remove it once dependents have updated their `composer.json`).
- v2.0.0 itself can ship from the monorepo or from the split repo — both are workable. Decide based on whichever sequencing has lower risk when v2 is close to release.

### Service provider

`LunarFilamentServiceProvider` registers:

- The Livewire synthesizers (currently registered in `LunarPanelProvider::register()` via `Livewire::propertySynthesizer(…)` — those move out).
- The bridge's translations (`loadTranslationsFrom(__DIR__.'/../resources/lang', 'lunar-filament')`).
- The bridge's views (`loadViewsFrom(…, 'lunar-filament')`).
- Publish stanzas for the views and lang files under the `lunar-filament` tag.
- A `Filament::registerWidgets([…])`-style registration **gated** so it only runs if the consumer has not opted out — downstream Filament panels that want to pick and choose widgets manage their own panel-level `widgets([…])` configuration.

Auto-discovered via Laravel's package discovery (`extra.laravel.providers`).

### Translation strategy

Every translation key moved from `packages/admin/resources/lang/{locale}/` moves to `packages/filament/resources/lang/{locale}/` under the new namespace `lunar-filament::`. Calls to `__('lunarpanel::…')` inside the moved files become `__('lunar-filament::…')`. The admin package keeps its own `lunarpanel::` namespace for the shell strings that remain. Both packages ship all 16 locales — English first, then mirror the keys across `ar, bg, de, es, fa, fr, hr, hu, mn, nl, pl, pt_BR, ro, tr, vi` (English value acceptable as a placeholder where no translation exists yet).

### Rector rules (lands in `lunarphp/upgrade`)

- Namespace rewrites for every moved class — `Lunar\Admin\Support\Forms\Components\*` → `Lunar\Filament\Forms\Components\*`, and equivalents for tables/infolists/field types/synthesizers/widgets/schemas/tables.
- Translation key rewrites — `lunarpanel::forms.attributes.*` → `lunar-filament::forms.attributes.*` (and the equivalents) for any keys that move with their owning component. Scoped to user-published lang files.
- Blade view path rewrites for components published into `resources/views/vendor/lunarpanel/forms/components/…` that now live under `resources/views/vendor/lunar-filament/forms/components/…`.
- No method-signature changes — component APIs are preserved verbatim across the move.

### PR slicing

One PR per logical group. Each PR moves code, updates internal call sites in `packages/admin/` to the new namespace, lands the matching Rector rules, and runs the admin suite green before merging.

1. **Bridge skeleton** — `packages/filament/` directory, `composer.json`, service provider, empty namespace, monorepo-builder wiring, `tests/Filament/` test suite scaffold. No code moves yet. Verifies the package boots in isolation against Orchestra Testbench.
2. **Generic primitives** — Forms/Components, Tables/Columns, Tables/Components, Tables/Actions, Infolists/Components.
3. **Attribute system** — FieldTypes, Synthesizers, `Support/Forms/AttributeData.php`. Single PR because they're tightly coupled.
4. **Widgets** — Dashboard widgets and the product variant switcher widget.
5. **Schemas/Tables/RelationManagers — catalog family** — Brand, Product, Tag, Collection, CollectionGroup, ProductType, ProductOption (+ values relation manager), ProductVariant, AttributeGroup (+ attributes relation manager), Attribute. Product's customer-group and customer-group-pricing relation managers move with it.
6. **Schemas/Tables/RelationManagers — sales family** — Order, Customer (+ address/orders/user relation managers), CustomerGroup, Discount (+ the eight discount limitation/condition/reward relation managers).
7. **Schemas/Tables/RelationManagers — settings family** — Channel, Currency, Language, Staff, TaxClass, TaxRate (+ amounts relation manager), TaxZone, Activity.
8. **Extension hook registry** — extract `ComponentExtensions` registry into the bridge, point `LunarPanel::extensions([…])` at it via a proxy. Lands after the schemas so the registry has somewhere to register against.
9. **Publishable stubs** — wire up `vendor:publish --tag=lunar-filament.schemas`, runtime resolution that prefers published classes, and the documentation distinguishing publish vs. extension vs. subclass-and-rebind. Lands last so the publishable surface covers every class moved by PRs 5–7.

`packages/table-rate-shipping` resources are out of scope for this spec — they stay self-contained inside their own package, with their own `Schemas/` and `Tables/` directories. The bridge is for cross-package primitives, not for relocating every plugin's resources.

## Alternatives considered

- **Move everything Filament-shaped into the bridge, including `LunarPanelManager` and the `{Resource}.php` entries.** Rejected — that erases the line between "bridge of primitives" and "turnkey admin shell". v2 needs both to coexist; collapsing them now means downstream apps lose the easy upgrade path of "keep using the admin as-is".
- **Move only the generic primitives (form/table/infolist components, field types, widgets); leave the per-resource schemas/tables in admin.** Rejected — the schemas/tables are the most valuable composable building blocks. Leaving them in admin means a developer building their own panel cannot drop in "Lunar's product form" without buying into the admin shell, which is the exact unblock the flight plan calls for.
- **Wait until v3 to extract the bridge.** Rejected — v3 phases out the admin shell, so the bridge has to exist by then. Birthing it inside v2 gives a transitional period where both ship side by side and users migrate at their own pace.
- **Publish the bridge as a stubbable starter kit (publishable resources) instead of a runtime package.** Rejected — publishable stubs decouple users from upstream improvements; every Filament version bump in the bridge would require the user to re-publish. A runtime package keeps users patched on minor releases. A publishable starter kit can layer on top later (mentioned in Open questions).

## Migration impact

- **Database**: none.
- **Public contract surface**: every moved class changes namespace. Every consumer that imports `Lunar\Admin\Support\Forms\Components\Attributes` (or equivalent) needs to update. Rector covers it. The functional API of each moved class is unchanged — same constructor, same methods, same options.
- **Upgrade path for v1.x consumers**: combined with the [[0002-core-namespace]] move and the [[0005-filament-v5-schemas-refactor]] renames, this is part of the single v1 → v2 Rector pass. Users running `php artisan lunar:upgrade` get all three sets of rewrites applied together.
- **Translations**: every key under `resources/lang/{locale}/forms/`, `tables/`, `infolists/`, `fieldtypes/`, `widgets/` moves with its component to the bridge's `lunar-filament::` namespace. All 16 locales updated. Admin keeps `lunarpanel::` for the strings tied to the shell (navigation, dashboard headings, cluster labels, command output).
- **Filament / admin**: this _is_ the admin change.
- **Plugins**: third-party plugins that imported moved classes break and need their namespace updated. The bridge becomes the recommended dependency for new plugins; existing plugins are unlikely to be affected because today they typically depend on `lunarphp/lunar` for the full admin and that dependency continues to satisfy them transitively via the bridge.

## Open questions

- **Filament plugin requires**: `awcodes/shout`, `awcodes/filament-badgeable-column`, `technikermathe/blade-lucide-icons`, `leandrocfe/filament-apex-charts`, `filament/spatie-laravel-media-library-plugin` all move to the bridge. Confirm none of them have admin-shell-only consumers that would orphan the dependency.
- **Tests**: the bridge needs Pest tests that boot under Orchestra Testbench **without** the admin panel, to prove the components are usable standalone. Today the admin suite runs against a Lunar-panel-shaped Filament boot. What's the smallest Testbench setup that exercises a Filament component without `LunarPanel`? Tracked as a PR 1 acceptance criterion.
- **Publishable starter pages/resources**: the flight plan hints at "perhaps having some pre-made Filament resources/pages that you can publish to get a head start." This spec covers publishable **schemas, tables, infolists, and relation managers**, which is most of the value. Publishable full `{Resource}.php` + `Pages/*` stubs (so a downstream dev gets a complete Lunar-style resource they can own) is out of scope here, worth scoping in a follow-up spec once the runtime bridge has shipped.

## Resolved questions

- **Schemas / Tables / Infolists / RelationManagers all move to the bridge** — confirmed. Plus a publishable-stub mechanism (`vendor:publish --tag=lunar-filament.schemas`) so downstream devs can take ownership of any of these files and make them their own. Runtime resolution prefers the published copy.
- **Namespace `Lunar\Filament\…` and facade `LunarFilament::extensions([…])`** — confirmed. Symmetric with `Lunar\Admin` / `LunarPanel`.
- **RelationManagers move with the schemas** — all 17 existing managers are commerce-model relations and move with their schema/table family in PRs 5–7.
- **Repo split trigger**: when **stable v2** ships. Not gated on API stability metrics or Filament's release cadence — v2.0.0 is the natural cutover.
- **`LunarPanel::extensions([…])` proxy** lives in admin; the registry lives in the bridge. The bridge owns its own extension points for the components/widgets/schemas it ships; admin owns its own extension points for the pages and resource shells it ships. The proxy on `LunarPanel` exists for backward compatibility during v2 and is removed when the admin shell phases out in v3.

## References

- [Flight plan log](https://docs.lunarphp.com/logs/flight-plan) — long-term direction for the Filament bridge package
- [[0005-filament-v5-schemas-refactor]] (completed) — created the split-class shape that this spec extracts
- [[0004-filament-v5-upgrade]] (completed) — Filament v5 baseline
- [[0001-upgrade-package]] (completed) — Rector host for the namespace rewrites
- [[0002-core-namespace]] (completed) — prior art for a wholesale namespace rename via Rector
- `packages/lunar/packages/admin/src/Support/` — directories listed under "What moves"
- `packages/lunar/packages/admin/src/Filament/Widgets/` — dashboard and product widgets to move
- `packages/lunar/packages/admin/src/Filament/Resources/{Resource}/Schemas/` and `Tables/` — schema/table classes to move
- `packages/lunar/packages/admin/src/LunarPanelManager.php` — panel manager and current extension registry, stays in admin
- `packages/lunar/monorepo-builder.php` — monorepo wiring; investigate per-package version policy
- Filament v5 panels overview: https://filamentphp.com/docs/5.x/panels/installation
