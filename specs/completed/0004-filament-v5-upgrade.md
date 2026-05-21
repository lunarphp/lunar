# 0004 — Filament v5 upgrade

- Status: completed
- Author: Glenn Jacobs
- Created: 2026-05-21
- TODO item: "Filament v5 upgrade"

## Problem

The admin package (`packages/lunar/packages/admin`) currently requires `filament/filament: ^4.1`. v2 of Lunar is the right (and only) window to take a major Filament bump alongside the rest of the breaking changes; shipping v2 on Filament v4 would lock the admin to a soon-to-be-previous major for the lifetime of the v2 line.

Compared with the v3 → v4 jump, v4 → v5 is a small step: most of the resource/page/widget API is stable, the schemas/forms/tables/infolists split introduced in v4 stays, and the bulk of changes are namespace tidy-ups, deprecation removals, and minor signature changes that `filament/upgrade` rewrites automatically.

## Proposal

Bump `filament/filament` to `^5.0` in `packages/admin/composer.json` (and the root `packages/lunar/composer.json`), run the official upgrader, and resolve the residual diff by hand.

Scope:

- `composer.json` constraints
  - `filament/filament`: `^4.1` → `^5.0`
  - `filament/spatie-laravel-media-library-plugin`: `^4.0` → `^5.0`
  - `filament/upgrade` (dev): `^4.0` → `^5.0`
  - Third-party plugins, bumped to whichever release line targets Filament v5:
    - `leandrocfe/filament-apex-charts`
    - `awcodes/shout`
    - `awcodes/filament-badgeable-column`
- Run `vendor/bin/filament-v5` (or whatever the official upgrade command ships as) against `packages/admin/src` to apply automated rewrites.
- Hand-fix anything the upgrader flags but cannot rewrite — expected hotspots:
  - `LunarPanelProvider` registration calls and any panel-level config.
  - Custom support classes under `src/Support/{Tables,Forms,Infolists,Actions,Pages,Resources,RelationManagers}` that subclass or wrap Filament base classes.
  - Synthesizers under `src/Support/Synthesizers` (Livewire glue tends to break across Filament majors).
  - Clusters, widgets, and custom pages in `src/Filament/{Clusters,Pages,Widgets}`.
- Re-run the package test suite (Pest) against the upgraded admin.
- Smoke-test the panel against a fresh install (resources list, create, edit, delete, table filters, bulk actions, media uploads, charts dashboard).

Coordination:

- Lands on top of the already-completed core namespace change ([[0002-core-namespace]]), so the v5 rewrites only need to consider the `\Lunar\Core\*` and `\Lunar\Admin\*` surface, not the legacy `\Lunar\*` root.
- Lands **before** the "Move core Filament e-commerce components to a new `lunarphp/filament` package" extraction so the new package is born on v5.
- The existing upgrade package ([[0001-upgrade-package]]) gains Rector rules only for any Lunar-specific admin API that changes shape during the upgrade (e.g. signature changes on `Lunar\Admin\Support\*` base classes that downstream resources extend). It does **not** ship Filament's own upgrade rules — users run `filament/upgrade` themselves as part of the v2 upgrade docs.

## Alternatives considered

- **Stay on Filament v4 for the v2 line.** Rejected — v2 is the breaking-release window; deferring means another forced upgrade mid-v2 or stagnating on an EOL Filament.
- **Fork the bits we need from Filament v4 into `lunarphp/filament` and pin the admin to v4 forever.** Rejected — enormous ongoing maintenance burden, defeats the point of building on Filament.
- **Do the v5 bump as part of the `lunarphp/filament` extraction PR.** Rejected — bundles two large, independently risky changes; review and bisect both become painful. Upgrade first, extract second.

## Migration impact

- **Database**: none.
- **Public contract surface**: any class in `Lunar\Admin\Support\*` that downstream code extends may change signature where Filament's parents have. Document the diff in the v2 upgrade guide.
- **Upgrade path**: end users run `composer require filament/upgrade --dev` and `vendor/bin/filament-v5` against their own app code as a documented step in the v1 → v2 guide. Lunar-specific signature changes are covered by [[0001-upgrade-package]] Rector rules.
- **Translations**: Filament's own translation keys may shift. Lunar admin overrides in `lang/` need an audit against the v5 key set across all 16 locales.
- **Filament / admin**: this _is_ the admin change.
- **Plugins**: third-party plugins gate the upgrade. If any of the listed plugins has no v5-compatible release by the time v2 ships, decide per plugin: wait, fork, drop the feature, or replace with an in-house equivalent.

## Open questions

- Exact Filament v5 release date vs. our v2 release target — if v5 slips, do we ship v2 on v4 and bump in a v2.x minor, or hold v2?
- Plugin readiness audit: which of `leandrocfe/filament-apex-charts`, `awcodes/shout`, `awcodes/filament-badgeable-column`, `filament/spatie-laravel-media-library-plugin` have v5-targeted branches? Each unanswered = one blocker.
- Do we drop `filament/upgrade` from `require-dev` after the bump, or keep it pinned to the next major for the next jump?
- Are there panel-level config changes in v5 that warrant a fresh `LunarPanelProvider` rather than patching the existing one?

## References

- `packages/lunar/packages/admin/composer.json` — current Filament constraints
- `packages/lunar/packages/admin/src/LunarPanelProvider.php` — panel entrypoint
- Filament upgrade tool: https://filamentphp.com/docs/5.x/upgrade-guide
- [[0001-upgrade-package]] (completed) — Rector host for any Lunar-specific admin API changes
- [[0002-core-namespace]] (completed) — already landed; this spec assumes the `\Lunar\Core\*` / `\Lunar\Admin\*` split
- TODO: "Move core Filament e-commerce components to a new `lunarphp/filament` package" — must land after this
