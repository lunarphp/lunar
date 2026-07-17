# Lunar v2

Lunar is a headless PHP/Laravel e-commerce package. This directory is the **package monorepo** (composed of sub-packages under `packages/`). It is consumed by a host Laravel app sitting at `../../` (the `lunar-v2` root), which is used to run, test, and exercise the package during development.

## Layout

- `packages/` — sub-packages published from this monorepo (`core`, `admin`, `filament`, `panel`, `panel-addon-example`, `search`, `stripe`, `paypal`, `opayo`, `table-rate-shipping`, `meilisearch`, `upgrade`, `demo-data`).
- `tests/` — Pest tests grouped per sub-package (admin, core, panel, search, stripe, shipping, upgrade); see `phpunit.xml` for the testsuites.
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
- **Public contract surface**: this package is consumed by downstream apps. Treat anything outside `Models/Concerns/`, `Support/`, and internal namespaces as a contract — breaking changes require a spec and a Rector rule in the `upgrade` package.
- **Migrations**: v2 ships a flat baseline. While v2 is in alpha, fold schema changes into the existing baseline migrations rather than adding change migrations; once v2 is released, schema changes go in new migrations instead. The `upgrade` package handles v1 → v2 transformations.
- **Filament**: v5 with the schemas refactor applied. Use schemas, not the deprecated wrapper traits.
- **Comments**: describe code in its own vocabulary — use the terms the API actually exposes (a `Fulfilment` is a "fulfilment", not a "parcel"). Don't introduce a metaphor that appears nowhere in the code. Keep comments and docblocks ASCII-only, except the established house style — em dash (`—`), arrow (`→`) in state-transition notes, and ellipsis (`…`). No other non-ASCII symbols (`§`, `Σ`, `⟹`, `≥`, emoji, …); spell them out (`section A`, `sum of`, `implies`, `>=`). This applies to comments, docblocks, and user-facing strings — the `resources/lang/` translation files are the only place non-ASCII text belongs.
- **Namespace**: `Lunar\Core\…` for core, `Lunar\Admin\…`, `Lunar\Search\…`, etc. for other sub-packages.
- **Service-layer DI**: every service-layer class (`Actions/`, `Managers/`, `Drivers/`, `Generators/`, `Orders/`, `Pricing/`, `Validation/`, `Telemetry/`, `Listeners/`, `Observers/`, `Pipelines/`) declares its collaborators on the constructor with PHP 8 promoted properties. Do not call `app(...)`, `App::make(...)`, `resolve(...)`, or a facade for a collaborator inside the service's methods — inject it. Per-call runtime arguments (a cart, a purchasable, a quantity) stay on the method signature. Bind every public service seam to an interface in `Contracts/` — action contracts in the dedicated `ActionServiceProvider` (its `$actions` map is the canonical list of swappable action seams), everything else via `LunarServiceProvider::registerManagers()` / `registerServices()`. Consumers swap an implementation by binding the interface in their own service provider — **do not** introduce `config('lunar.*', SomeClass::class)` substitution keys for class swaps; config is for values, the container is for substitutions. Actions expose a single `execute()` verb method and return the value the caller needs — no `make()`/`run()` static shortcuts. (An action may carry public *static* domain helpers alongside it — e.g. `CaptureOrder::canRun()` — but the instance entry point is always `execute()`.) `tests/core/Unit/ArchitectureTest.php` enforces that every `Actions/` class implements a contract, exposes `execute()`, and imports no facades.
- **Entry points**: every operation is an action class — that is the swap seam, not the ergonomic entry point. If an operation's first parameter is the model it operates on, expose it as a verb method on that model **and its contract**, one-line-delegating to the action contract (`$cart->createOrder()`, `$fulfilment->ship($tracking)`); call sites — Filament actions included — use the verb, never `app(SomeContract::class)->execute($model, …)`. Managers and facades are reserved for concerns that don't belong to one model instance: session state (`CartSession`, `StorefrontSession`), fluent context (`Pricing`, `Discounts`), driver resolution (`Payments`, `Taxes`), registries (manifests, `Carriers`). Never add a stateless manager/facade whose methods just forward model-first calls. Internal orchestration actions (rollup resolvers, recomputes) stay action-only — verbs are for public, consumer-facing operations.
- **Folder responsibilities** (`packages/core/src/`): each top-level folder names a single concern. `Casts/` holds Eloquent cast classes. `Contracts/` holds every interface — drop the `Interface` suffix when adding a new one. `DataObjects/` holds plain value containers and DTOs. `Drivers/` holds swappable driver implementations. `Enums/` holds PHP enums. `FieldTypes/` holds attribute field types plus the `Manifest` that catalogues them. `Manifests/` holds cross-domain manifests (`AttributeManifest`, `ModelManifest`, `ShippingManifest`). `Media/` holds media-definition classes. `Models/` holds Eloquent models extending `Models\Base`; `Models/Concerns/` is the home for traits that attach to Eloquent models (`HasChannels`, `HasMedia`, `HasPrices`, `IsLunarUser`, `Searchable`, …). `Modifiers/` holds the `Cart`, `CartLine`, `Order` and `Shipping` modifier abstracts and their collection wrappers. `Orders/` holds order-related services like `ReferenceGenerator`. `Telemetry/` holds the telemetry service and its insights provider. `ValueObjects/` holds richer immutable values (cart breakdowns, free items, promotions).

## Tests

- **Run one suite at a time, exactly as CI does.** Each suite is a separate job in `.github/workflows/tests.yml`; run them the same way locally rather than in a single combined pass. Do **not** run `vendor/bin/pest` with no `--testsuite`.
- From inside this package: `vendor/bin/pest --testsuite <name> --parallel` (matches CI). Suites (the CI matrix): `core`, `admin`, `panel`, `filament`, `shipping`, `stripe`, `search`, `upgrade`. (A `demo-data` suite also exists in `phpunit.xml` but is not in the CI matrix.)
- Full local sweep — loop the suites: `for s in core admin panel filament shipping stripe search upgrade; do vendor/bin/pest --testsuite "$s" --parallel || break; done`.
- Panel frontend checks run via npm from `packages/panel` (own dependency tree, `npm install` there first): `npm test` (vitest), `npm run type-check`, `npm run build`. The example add-on builds through the npm workspace at this directory's root: `npm run build --workspace @lunarphp/panel-addon-example`. CI runs all four in the `panel-js` job.
- Cross-database tests run as a group, not a suite: `vendor/bin/pest --group=cross-db --parallel --do-not-fail-on-empty-test-suite`.
- `php artisan test` from the host app runs only the host app's own tests, not these package suites — it is not a substitute for running the suites above.
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

## Merging

- **Never merge a branch or PR automatically.** Do not run `gh pr merge`, `git merge`, or push to a shared branch (`1.x`, `2.x`, …) on your own. Merging is always the human's call. You may open/update PRs, run checks, and report status — but stop short of merging and let the maintainer click the button.
- PRs target the relevant version line: v2 work merges into `2.x`, not `1.x`.
