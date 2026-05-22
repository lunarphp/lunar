# 0006 — Extract `lunarphp/filament` bridge package and reshape the install model

- Status: accepted
- Author: Glenn Jacobs
- Created: 2026-05-22
- TODO item: "Move core Filament e-commerce components to a new `lunarphp/filament` package"

## Problem

The admin panel ([`lunarphp/lunar`](../packages/admin/composer.json)) is a single monolithic Composer package that bundles two distinct things:

1. **The Lunar admin shell** — a turnkey Filament panel: `LunarPanelManager`, `LunarPanelProvider`, navigation, branding, auth, the Dashboard page, cluster pages, the full set of `{Model}Resource.php` thin entries, admin-only Livewire components.
2. **The reusable Filament building blocks** — generic form components, table columns, infolist components, attribute field types, Livewire synthesizers, dashboard widgets, and (post-[[0005-filament-v5-schemas-refactor]]) the per-resource `{Resource}Form` / `{Resource}Table` / `{Resource}Infolist` schema classes.

The reusable layer is the interesting one for downstream developers. Today a developer who wants a Filament admin tailored to their own brand has two options: take Lunar's whole panel and override pieces of it via `LunarPanel::extensions([…])`, or rebuild the equivalents from scratch. There is no path to "use my own Filament panel, but compose Lunar's product form / order table / dashboard widgets into it" — because those components are gated behind the `lunarphp/lunar` install and a Lunar-shaped panel boot.

That coupling also pins the admin shell to the Filament release cadence. When Filament cuts a new major (v5 → v6), Lunar has to ship a coordinated release across core, admin, and every other package even though only the Filament-touching code needs to move.

A second, related problem: **the current install model hides the core package from new users.** The de facto install command is `composer require lunarphp/lunar`, which transitively pulls in `lunarphp/core`. New developers reading the docs treat "Lunar" and "the admin panel" as the same thing — most don't realise `lunarphp/core` is independently installable, headless, and viable for a custom storefront / custom admin / API-only deployment. The naming actively works against the headless positioning.

The [flight plan](https://docs.lunarphp.com/logs/flight-plan) declares the long-term direction: Lunar provides the commerce primitives; the framework you build your admin in stays under your control. By v3 the existing admin shell phases out, but the bridge package stays. v2 is the window where the bridge is born alongside the existing admin so consumers can migrate without forced rewrites — and it's the right moment to fix the install-model naming, since v2 is already the breaking-change window.

## Proposal

Two changes ship together:

1. **Extract a new `lunarphp/filament` bridge package** that owns every Filament-side primitive with value outside the Lunar admin shell.
2. **Rename `lunarphp/lunar` → `lunarphp/admin`** and document a new install model where developers install `lunarphp/core` plus their chosen panel (`lunarphp/admin`, `lunarphp/filament`, or the future `lunarphp/panel`) as separate, explicit requires.

### Installation model

After this spec, the canonical install instructions are:

```bash
# Always:
composer require lunarphp/core

# Plus one of:
composer require lunarphp/admin       # Turnkey Filament admin panel (today's experience)
composer require lunarphp/filament    # Components/widgets/schemas for your own Filament panel
composer require lunarphp/panel       # New Inertia.js admin panel (future, separate spec)
```

Each option carries its own installation documentation. `lunarphp/core` docs cover the headless install — services, models, contracts, configuration — with no panel assumed. The three panel packages each carry installation docs that pick up from "you've already got core installed".

Effects:

- Core's existence and capabilities become visible at the first step of every install path, fixing the "I didn't know I could install Lunar without Filament" problem.
- The three panel packages are equal siblings — none of them is the implicit default.
- Choosing between panels is an explicit, documented decision, not a side-effect of which getting-started page the developer landed on.
- New panels (e.g. `lunarphp/panel`) drop in alongside the existing two without disrupting their consumers.

### `lunarphp/filament` bridge package

A new first-party package at `packages/filament/` in this monorepo. It owns every Filament-side primitive that has value outside the Lunar admin shell. The renamed admin package (`lunarphp/admin`) keeps the shell pieces and depends on the new bridge for its components.

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

### What stays in `lunarphp/admin` (admin shell)

- `LunarPanelManager`, `LunarPanelProvider`, `Auth/`, `Models/Staff`, `Console/Commands/MakeLunarAdminCommand`.
- `Filament/Resources/{Resource}.php` — thin entries with `getModel`, navigation, page registration. The `form()` / `table()` / `infolist()` methods become one-line delegates to the bridge classes: `return \Lunar\Filament\Schemas\Brand\BrandForm::configure($schema);`.
- `Filament/Resources/{Resource}/Pages/*` — page classes are tied to the Lunar admin panel's UX and stay.
- `Filament/Pages/Dashboard.php`, `Filament/Clusters/Taxes.php`, `Filament/AvatarProviders/*`.
- `Support/Resources/BaseResource.php` + concerns (`HasLunarPermissions`, `ResolvesModelContract`, `HasScoutGlobalSearch`).
- `Support/Pages/*` — admin page base classes and concerns.
- `Livewire/*` — admin-specific Livewire components.

### Extension hook system

[[0005-filament-v5-schemas-refactor]] established `LunarPanel::extensions([{Resource}Form::class => […]])` as the registration surface, with stackable extensions targeting the split-class form/table/infolist classes. Those target classes now live in the bridge package, but `LunarPanel` lives in `lunarphp/admin`. Two changes:

1. **Hook registry moves to the bridge.** A new facade `LunarFilament::extensions([…])` owns the registry of stackable hooks against bridge schema/table classes. The bridge classes call into it from their `configure()` methods. This is the canonical entry point for developers using `lunarphp/filament` in their own Filament panel.
2. **`LunarPanel::extensions([…])` stays in `lunarphp/admin` and proxies into the bridge registry.** Existing callers in `lunarphp/admin` consumers keep working without a code change. Documented as the entry point for users of the Lunar admin shell; downstream developers building their own panel use `LunarFilament::extensions([…])` directly.

Both facades remain in v2. The proxy on `LunarPanel` is marked for removal in v3 when the admin shell phases out.

### Composer and monorepo wiring

- `packages/filament/composer.json` — `lunarphp/filament`, requires `lunarphp/core: self.version`, `filament/filament: ^5.0`, plus the Filament plugin packages the moved code uses (`filament/spatie-laravel-media-library-plugin`, `leandrocfe/filament-apex-charts`, `awcodes/shout`, `awcodes/filament-badgeable-column`, `technikermathe/blade-lucide-icons`).
- `packages/admin/composer.json` — `name` field changes from `lunarphp/lunar` to `lunarphp/admin`. Adds `lunarphp/filament: self.version` to its requires; drops the Filament plugin requires that now belong to the bridge (admin keeps Filament itself plus `spatie/laravel-permission`, `barryvdh/laravel-dompdf` since those are admin-shell-only). The directory stays at `packages/admin/` — only the published Composer name changes.
- `packages/table-rate-shipping/composer.json` — `lunarphp/lunar` require becomes `lunarphp/admin`. Same applies to any other sibling package that depends on the admin shell (audit at PR time).
- `monorepo-builder.php` — no changes needed; `Option::PACKAGE_DIRECTORIES` already scans `packages/*`.
- `packages/lunar/composer.json` (root meta, `lunarphp/lunarmono`) — adds `lunarphp/filament: self.version` to the umbrella requires.
- **Old `lunarphp/lunar` Packagist name**: marked `"abandoned": "lunarphp/admin"` in its final tagged release on the v1.x line. Composer surfaces this as an `abandoned` warning during `composer update`, pointing users at the new name. The release notes for v2.0.0 and the upgrade docs explain the rename and the new install model. No backwards-compat meta-package — keeping a `lunarphp/lunar` meta that pulls in `lunarphp/core` + `lunarphp/admin` would re-create the original "core is invisible" problem this spec is trying to fix.
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

### Rector rules and upgrade actions (land in `lunarphp/upgrade`)

- Namespace rewrites for every moved class — `Lunar\Admin\Support\Forms\Components\*` → `Lunar\Filament\Forms\Components\*`, and equivalents for tables/infolists/field types/synthesizers/widgets/schemas/tables.
- Translation key rewrites — `lunarpanel::forms.attributes.*` → `lunar-filament::forms.attributes.*` (and the equivalents) for any keys that move with their owning component. Scoped to user-published lang files.
- Blade view path rewrites for components published into `resources/views/vendor/lunarpanel/forms/components/…` that now live under `resources/views/vendor/lunar-filament/forms/components/…`.
- **`composer.json` require rewrites** — the upgrade command rewrites consumer `composer.json` to swap `lunarphp/lunar` → `lunarphp/admin` and adds `lunarphp/core` explicitly if it was only present transitively. After the rewrite, the upgrade command prints a `composer update` instruction. Implemented as a structured JSON edit (not Rector) because Rector targets PHP AST.
- No method-signature changes — component APIs are preserved verbatim across the move.

### PR slicing

One PR per logical group. Each PR moves code, updates internal call sites in `packages/admin/` to the new namespace, lands the matching Rector rules, and runs the admin suite green before merging.

0. **Rename `lunarphp/lunar` → `lunarphp/admin`** — `packages/admin/composer.json` name change; update every sibling package require (`lunarphp/table-rate-shipping` and any other internal consumer); update root meta `lunarphp/lunarmono` require; update CI, docs references inside the monorepo, and `CLAUDE.md` mentions. No code moves. Lands first so subsequent PRs target the correct package name in their composer.json edits. Tagged release of the v1.x line marks `lunarphp/lunar` as `abandoned` in favour of `lunarphp/admin`.
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
- **Keep `lunarphp/lunar` as the admin Composer name.** Rejected — the umbrella-name-as-admin-name is the reason new developers conflate Lunar with the Filament admin and miss that `lunarphp/core` is independently installable. v2 is the breaking-change window; doing this rename later is more disruptive than doing it now.
- **Repurpose `lunarphp/lunar` as a meta-package** that requires `lunarphp/core` + `lunarphp/admin` for backwards compat. Rejected — re-introduces the exact visibility problem the rename is fixing. A new developer running `composer require lunarphp/lunar` gets the bundle without seeing the constituent parts, the docs still have to caveat "or you can install core separately…", and the implicit-default panel framing returns. The abandoned-warning route is louder and self-correcting.
- **Split the install-model change into its own spec.** Rejected — the bridge package is one leaf of the new install model's three-leaf decision (admin / filament / panel). Documenting the bridge in isolation would force the docs to either pretend the wider model doesn't exist or duplicate the framing. Shipping them together gives a single coherent v2 release-note story.

## Migration impact

- **Database**: none.
- **Public contract surface**: every moved class changes namespace. Every consumer that imports `Lunar\Admin\Support\Forms\Components\Attributes` (or equivalent) needs to update. Rector covers it. The functional API of each moved class is unchanged — same constructor, same methods, same options.
- **Composer install surface**: `lunarphp/lunar` is renamed to `lunarphp/admin`. Consumer `composer.json` needs `lunarphp/lunar` → `lunarphp/admin` and an explicit `lunarphp/core` require. The upgrade command rewrites both. Final v1.x release of `lunarphp/lunar` is marked `abandoned` so users on stale lockfiles see a clear pointer.
- **Upgrade path for v1.x consumers**: combined with the [[0002-core-namespace]] move and the [[0005-filament-v5-schemas-refactor]] renames, this is part of the single v1 → v2 Rector pass. Users running `php artisan lunar:upgrade` get all three sets of rewrites applied together, plus the `composer.json` edit.
- **Documentation**: significant rework. `lunarphp/core` gains a top-level installation page covering the headless install. The three panel packages (`lunarphp/admin`, `lunarphp/filament`, `lunarphp/panel` when it exists) each carry their own install guide that picks up from "you've already got core installed". The current "Lunar installation" page becomes a chooser page that explains the three panel options and links to each. Tracked alongside the docs site work, not blocking the package PRs.
- **Translations**: every key under `resources/lang/{locale}/forms/`, `tables/`, `infolists/`, `fieldtypes/`, `widgets/` moves with its component to the bridge's `lunar-filament::` namespace. All 16 locales updated. Admin keeps `lunarpanel::` for the strings tied to the shell (navigation, dashboard headings, cluster labels, command output).
- **Filament / admin**: this _is_ the admin change.
- **Plugins**: third-party plugins that imported moved classes break and need their namespace updated. The bridge becomes the recommended dependency for new plugins. Plugins that today depend on `lunarphp/lunar` for the full admin continue to work — their composer require gets rewritten to `lunarphp/admin` by the upgrade command, and the bridge classes are reachable transitively via the renamed admin package.

## Open questions

- **Filament plugin requires**: `awcodes/shout`, `awcodes/filament-badgeable-column`, `technikermathe/blade-lucide-icons`, `leandrocfe/filament-apex-charts`, `filament/spatie-laravel-media-library-plugin` all move to the bridge. Confirm none of them have admin-shell-only consumers that would orphan the dependency.
- **Tests**: the bridge needs Pest tests that boot under Orchestra Testbench **without** the admin panel, to prove the components are usable standalone. Today the admin suite runs against a Lunar-panel-shaped Filament boot. What's the smallest Testbench setup that exercises a Filament component without `LunarPanel`? Tracked as a PR 1 acceptance criterion.
- **Publishable starter pages/resources**: the flight plan hints at "perhaps having some pre-made Filament resources/pages that you can publish to get a head start." This spec covers publishable **schemas, tables, infolists, and relation managers**, which is most of the value. Publishable full `{Resource}.php` + `Pages/*` stubs (so a downstream dev gets a complete Lunar-style resource they can own) is out of scope here, worth scoping in a follow-up spec once the runtime bridge has shipped.
- **Final v1.x `lunarphp/lunar` release**: do we cut a final `lunarphp/lunar` release on the `1.x` branch with `"abandoned": "lunarphp/admin"`, or just mark the existing Packagist entry abandoned via the maintainer UI? The release route gives `composer outdated` users a version bump signal too. Recommend cutting a release.
- **Repository structure for `lunarphp/admin`**: the directory stays at `packages/admin/`, but the published name changes. Should the directory eventually be renamed to match (e.g. `packages/admin/` already matches, no action needed — but worth confirming during PR 0). No other internal package has this mismatch.

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
- [[0001-upgrade-package]] (completed) — Rector host for the namespace rewrites and the `composer.json` rewriter for the `lunarphp/lunar` → `lunarphp/admin` rename
- [[0002-core-namespace]] (completed) — prior art for a wholesale namespace rename via Rector
- `packages/lunar/packages/admin/composer.json` — `name` field flips from `lunarphp/lunar` to `lunarphp/admin`
- `packages/lunar/packages/table-rate-shipping/composer.json` — sole internal consumer of the old `lunarphp/lunar` require; updated in lockstep
- `packages/lunar/packages/admin/src/Support/` — directories listed under "What moves"
- `packages/lunar/packages/admin/src/Filament/Widgets/` — dashboard and product widgets to move
- `packages/lunar/packages/admin/src/Filament/Resources/{Resource}/Schemas/` and `Tables/` — schema/table classes to move
- `packages/lunar/packages/admin/src/LunarPanelManager.php` — panel manager and current extension registry, stays in admin
- `packages/lunar/monorepo-builder.php` — monorepo wiring; bridge wears the shared version until the v2.0.0 repo split
- Future `lunarphp/panel` package (Inertia.js admin) — not part of this spec; will plug into the same three-leaf install model as the bridge
- Filament v5 panels overview: https://filamentphp.com/docs/5.x/panels/installation
