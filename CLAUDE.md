# Lunar v2

Lunar is a headless PHP/Laravel e-commerce package. This directory is the **package monorepo** (composed of sub-packages under `packages/`). It is consumed by a host Laravel app sitting at `../../` (the `lunar-v2` root), which is used to run, test, and exercise the package during development.

## Layout

- `packages/` — sub-packages published from this monorepo (`core`, `admin`, `filament`, `search`, `stripe`, `paypal`, `opayo`, `table-rate-shipping`, `meilisearch`, `upgrade`).
- `tests/` — Pest tests grouped per sub-package (admin, core, search, stripe, shipping, upgrade); see `phpunit.xml` for the testsuites.
- `specs/` — design docs for v2 work (see below).
- `TODO.md` — the v2 work tracker.
- `composer.json` / `monorepo-builder.php` — the monorepo config; sub-packages are split out on release.

## The host Laravel app

The directory **above this one** (`/Users/glenn/Herd/lunar-v2/`) is a standard Laravel app that pulls this package in as a local Composer path repo.

- Served by Laravel Herd at `https://lunar-v2.test` (use Boost's `get-absolute-url` to confirm).
- Owns Laravel Boost, the MCP config (`.mcp.json`), env, migrations table, and the project-wide `CLAUDE.md` with Laravel/Filament/PHP rules — those still apply when editing this package.
- Use it to run Artisan, exercise the admin panel, browse the storefront, and verify changes end-to-end. Run `php artisan` from the host app, not from here.

## Spec-driven development

Every non-trivial change to Lunar v2 starts as a spec.

- `TODO.md` lists outstanding work, ideas, and what is done.
- `specs/NNNN-short-slug.md` — one file per TODO item, sequentially numbered. Use `specs/0000-template.md` as the starting point.
- `specs/completed/` — specs whose implementation has shipped; move them there once merged.
- See `specs/README.md` for the status lifecycle (`draft` → `proposed` → `accepted` → `implemented` / `superseded`).
- A spec should land (reviewed + merged) before its implementation work begins. Keep specs in present tense, focused on the proposed change.

When asked to start a new piece of work that isn't already specced, write the spec first and wait for review.

## Conventions specific to Lunar

- **Translations**: 16 locales live under each sub-package's `resources/lang/` (`ar, bg, de, en, es, fa, fr, hr, hu, mn, nl, pl, pt_BR, ro, tr, vi`). When adding or renaming a translation key, update **every** locale — English first, then mirror the key (English value is acceptable as a placeholder) across the other 15.
- **Public contract surface**: this package is consumed by downstream apps. Treat anything outside `Concerns/`, `Support/`, and internal namespaces as a contract — breaking changes require a spec and a Rector rule in the `upgrade` package.
- **Migrations**: v2 ships a flat baseline (spec 0003). Schema changes go in a new migration; do not edit the baseline. The `upgrade` package handles v1 → v2 transformations.
- **Filament**: v5 with the schemas refactor applied (spec 0005). Use schemas, not the deprecated wrapper traits.
- **Namespace**: `Lunar\Core\…` for core (spec 0002), `Lunar\Admin\…`, `Lunar\Search\…`, etc. for other sub-packages.
- **Folder responsibilities** (`packages/core/src/`, spec 0013): each top-level folder names a single concern. `Casts/` holds Eloquent cast classes. `Concerns/` holds behaviour traits (the trait equivalent of `Contracts/`). `Contracts/` holds every interface — drop the `Interface` suffix when adding a new one. `DataObjects/` holds plain value containers and DTOs. `Drivers/` holds swappable driver implementations. `Enums/` holds PHP enums. `FieldTypes/` holds attribute field types plus the `Manifest` that catalogues them. `Manifests/` holds cross-domain manifests (`AttributeManifest`, `ModelManifest`, `ShippingManifest`). `Media/` holds media-definition classes. `Models/` holds Eloquent models extending `Models\Base`. `Modifiers/` holds the `Cart`, `CartLine`, `Order` and `Shipping` modifier abstracts and their collection wrappers. `Orders/` holds order-related services like `ReferenceGenerator`. `Telemetry/` holds the telemetry service and its insights provider. `ValueObjects/` holds richer immutable values (cart breakdowns, free items, promotions).

## Tests

- `php artisan test --compact` from the host app (preferred — exercises the real Laravel boot), or `vendor/bin/pest` from inside this package against Orchestra Testbench.
- Filter by suite: `vendor/bin/pest --testsuite=core`.
- Use factories; do not invent ad-hoc test data when a factory state already covers it.

## Static analysis (REQUIRED)

PHPStan (via Larastan, level 0) is part of the test pipeline and **must pass before finalizing any PHP change**. Treat it as non-optional, alongside Pint and Pest.

- Run from this directory: `vendor/bin/phpstan analyse --no-progress`.
- Configuration: `phpstan.neon.dist` — scans every `packages/*/src`, excludes `tests/`, `config/`, and vendor paths.
- The `composer test` script chains pint check + pest + phpstan; CI runs the same. Skipping phpstan locally just defers the failure to CI.
- Fix the underlying type/contract issue. Do not add `@phpstan-ignore`, baseline entries, `assert()`, or inline `@var` to silence errors unless you have a specific reason (and call it out in the PR).
- When introducing new public surface (selectors, schemas, helpers), add type hints and array-shape PHPDoc so phpstan can verify call sites without baseline noise.

## Reviewing changes

Run the `lunar:pr-review` skill (or `/lunar:pr-review`) before opening a PR — it checks translation completeness across the 16 locales, missing tests/factories, migration safety, Filament contract usage, and breaking-change risk on the public surface.
