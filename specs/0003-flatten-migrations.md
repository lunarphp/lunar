# 0003 — Flatten v1.x migrations into a v2 baseline

- Status: draft
- Author: Glenn Jacobs
- Created: 2026-05-20
- TODO item: "Flatten v1.x migrations into a v2 baseline"

## Problem

Lunar v1.x ships a long, chronological chain of migrations accumulated over years. Fresh v2 installs would otherwise replay that entire history just to land on the v2 schema — including migrations that add a column, rename it, and later drop it. v2 is a wholesale breaking release that rewrites most of the schema anyway (attributes remodel, state machines, `compare_price` → `list_price`, shipping storage, `public_id` additions, region/vendor concepts), so carrying the v1 chain forward serves no one:

- New installs pay for years of churn on every `migrate:fresh`.
- The migration files stop being a useful description of the schema; the schema lives implicitly in the diff chain.
- Authoring new v2.x migrations against a clear baseline is easier than reasoning about a chain that includes superseded states.

## Proposal

Ship v2 with a single flat set of migration files that produce the final v2 schema directly. No incremental history before v2.0.0.

Approach:

- Delete every v1.x migration from the package.
- Author one migration per table (or small cluster of tightly related tables) under `database/migrations/`, dated `2026_01_01_000000` onward in dependency order.
- Each file represents the v2 schema for that table as it should exist at install time — no `Schema::table(...)` modifications of earlier files in the same set.
- Run `php artisan migrate` on a fresh database during CI to assert the resulting schema matches the application's expectations.

Coordination with the upgrade path:

- The flattened files become the canonical v2 baseline.
- [[0001-upgrade-package]] owns the v1 → v2 schema transformation migration that takes an existing v1.x database to the same state these files would produce on a fresh install.
- After the transformation runs, the upgrade tool rewrites the user's `migrations` table: removes all v1 Lunar entries, inserts entries for every v2 baseline file marked as already-run.

## Alternatives considered

- **Keep the v1 chain and append v2 migrations on top.** Rejected — perpetuates the existing churn, makes the schema harder to read, and gives no incentive to ever clean up.
- **Use Laravel's `schema:dump` to produce a `schema.sql` snapshot.** Rejected — `schema.sql` is database-vendor-specific; the package needs to support multiple drivers (MySQL, PostgreSQL, SQLite) without shipping three dumps and a switch.
- **Squash only some tables.** Rejected — partial flattening leaves the same readability problem and adds a "which tables are squashed?" rule every contributor has to remember.

## Migration impact

- **Database**: this spec _is_ a database change. Fresh installs run the new baseline. Existing v1.x installs are handled entirely by the upgrade package.
- **Public contract surface**: no runtime API change. Anything that referenced specific v1 migration class names (rare, but possible in user tests or seeders) breaks — covered by Rector rules in the upgrade package.
- **Upgrade path**: see [[0001-upgrade-package]] for the schema transformation and ledger rewrite.
- **Translations**: none.
- **Filament / admin**: none directly; downstream specs that change schema land their changes by editing the baseline files, not by adding follow-up migrations, until v2.0.0 ships.

## Open questions

- Cutoff policy: once v2.0.0 ships, all subsequent changes are normal additive migrations. Confirm we are happy editing baseline files freely during the v2 development window (pre-release), and frozen thereafter.
- Do we keep migration filenames stable across the v2 dev window, or accept renames as the schema settles? Stable names make the upgrade package's ledger inserts easier to write.
- Test database for the package's own test suite: does it use the baseline migrations directly, or a faster `RefreshDatabase`-with-cached-schema approach?

## References

- [[0001-upgrade-package]] — owns the v1 → v2 transformation and `migrations` ledger rewrite that lets existing installs adopt this baseline
- Laravel migration squashing: https://laravel.com/docs/migrations#squashing-migrations
