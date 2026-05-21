# 0001 — Upgrade package

- Status: accepted
- Author: Glenn Jacobs
- Created: 2026-05-20
- TODO item: "Add an Upgrade package for those migrating from v1.x (using Rector)"

## Problem

v2 is a wholesale breaking release. The work tracked in `TODO.md` rewrites the public contract surface in several ways simultaneously: namespace move ([[0002-core-namespace]]), attributes remodel, state machines replacing soft-deletes, `compare_price` → `list_price`, shipping option storage, dropping the polymorphic shipping morph, region/vendor concepts, etc.

Without a dedicated upgrade tool:

- Every v1.x consumer has to hand-port their app, config, migrations, and database state.
- Each breaking spec has to invent its own upgrade story, leading to inconsistency and gaps.
- We have no shared place to encode "if you were on v1.x doing X, here is the exact transformation."

Building the upgrade package first means every subsequent v2 spec can land its Rector rules and data-migration steps into an existing home, with a single CLI entry point that users already know.

## Proposal

A new first-party package, `lunarphp/upgrade`, installed by v1.x users as a dev dependency to perform the v1 → v2 migration. It is not a runtime dependency of v2 itself.

Scope:

- **Code rewrites** via [Rector](https://github.com/rectorphp/rector) rules. Each v2 breaking spec contributes its own rule set, registered under a named set (e.g. `LunarSetList::V1_TO_V2`).
- **Config rewrites** for files published from `config/lunar/*`.
- **Database transformations** as standard Laravel migrations, shipped by the package and runnable via `php artisan lunar:upgrade`. Used for class-string rewrites in morph maps, column renames, enum value migration, etc.
- **Migrations ledger reset** to align upgraded installs with v2's flattened baseline (see [[0003-flatten-migrations]]). The upgrade transforms the existing v1 schema in place, then rewrites the `migrations` table to mark the new v2 baseline files as already-run so future v2.x migrations layer on cleanly.
- **Published asset rewrites** (Filament resources, Blade views) where users have published and customised them.
- **A guided CLI** (`php artisan lunar:upgrade`) that runs the steps in order, with `--dry-run`, per-step opt-out, and a clear report of what changed and what needs manual review.

Package layout:

```
packages/upgrade/
    composer.json              # lunarphp/upgrade
    config/rector.php          # entry point importing rule sets
    src/
        Console/UpgradeCommand.php
        Rector/                # one subdir per concern
            Namespace/
            Attributes/
            Pricing/
            Shipping/
            States/
        Database/
            Migrations/        # data transformations
        Support/
            ClassStringRewriter.php   # shared helper for morph maps etc.
    tests/
```

Each v2 spec that introduces a breaking change lists its upgrade contributions in its own `Migration impact` section and lands the corresponding Rector rule + (if needed) data migration in this package as part of the same PR.

The CLI command flow:

1. Detect installed Lunar version; refuse to run if not on the latest v1.x.
2. Verify all v1.x migrations are applied (the schema transformation assumes a known starting state).
3. Print the plan (numbered steps, each tied to a spec).
4. For each step: run Rector against `app/`, `config/`, `database/`, and any user-configured paths; run the data migration; report.
5. Apply the v1 → v2 schema transformation migration in a single transaction (where the driver supports DDL transactions), then rewrite the `migrations` table: remove v1 Lunar entries, insert v2 baseline entries marked as run.
6. Final manual-action checklist for things we cannot automate (e.g. user-defined discount conditions whose class strings live in user data).

## Alternatives considered

- **Ship Rector rules inside `lunarphp/core` and skip the data-migration tooling.** Rejected — bloats the runtime package with dev tooling, and leaves data migrations as the user's problem.
- **A one-shot upgrade script (not a package).** Rejected — no versioning, no tests, no contribution surface for future v2.x → v3 work; the package can be reused for the next major.
- **Document the upgrade and let users do it by hand.** Rejected — the cumulative breaking surface across v2 is too large; hand-porting is error-prone, especially for class strings persisted in user databases.

## Migration impact

This spec _is_ the migration impact for the other specs. It has no v1.x users of its own.

- **Public contract surface**: new package, no existing surface.
- **Database**: ships migrations that user apps run during upgrade; no schema owned by the package itself. Coordinates with [[0003-flatten-migrations]] to rewrite the user's `migrations` table after transforming the schema.
- **Translations**: CLI output should be translatable, but is not part of the 16-locale storefront/admin set — keep upgrade strings in this package.
- **Filament / admin**: rules for published Filament resources/pages land here; coordinate with the Filament v5 upgrade spec when it exists.

## Open questions

- Do we support upgrading from arbitrary v1.x minor versions, or pin to "latest v1.x only"? Recommendation: pin, and have users run `composer update lunarphp/core` to the final v1.x first.
- Where do user-defined extension points (custom discount conditions, custom purchasables, custom shipping modifiers) get their class strings rewritten? Likely a config file users opt into.
- How do we test the upgrade against real v1.x apps — fixture apps in the package's tests, or a separate integration harness?
- Versioning policy after v2 ships: does this package keep accumulating rules for future majors, or do we cut a fresh upgrade package per major?
- How do we handle users who published v1.x Lunar migrations into their own `database/migrations` directory? The transformation has to detect and neutralise those before rewriting the ledger.

## References

- [[0002-core-namespace]] — the first concrete consumer of the upgrade package
- [[0003-flatten-migrations]] — defines the v2 baseline that this package's transformation step targets
- Rector docs: https://getrector.com/documentation
- TODO items that will contribute rules: namespace change, attributes remodel, state machines, `compare_price` → `list_price`, shipping option storage, region/vendor concepts, Filament v5
