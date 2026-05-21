# 0002 — Core namespace change

- Status: accepted
- Author: Glenn Jacobs
- Created: 2026-05-20
- TODO item: "Change to `\Lunar\Core` namespace"

## Problem

The core package (`packages/lunar/packages/core`) ships under the top-level `\Lunar\` namespace (`"Lunar\\": "src"` in `packages/core/composer.json`). The same root is also the natural home for cross-package types, the admin/Filament package, future first-party packages, and third-party extensions.

Owning the root namespace from one package:

- Conflates "the core e-commerce engine" with "the Lunar ecosystem", making it harder to extract or replace core without touching every consumer.
- Forces third-party packages to nest under `\Lunar\` even when they have no relationship to the core package's classes, encouraging accidental coupling.
- Blocks the planned split of Filament e-commerce components into `lunarphp/filament` (see TODO) from using a clean `\Lunar\Filament\` root, because that root is currently occupied by core classes.

## Proposal

Move every class shipped by `packages/core` from `\Lunar\` into `\Lunar\Core\`. The composer autoload entry becomes:

```json
"psr-4": {
    "Lunar\\Core\\": "src",
    "Lunar\\Core\\Database\\Factories\\": "database/factories"
}
```

After the move:

- `\Lunar\` is reserved as the ecosystem root and ships no classes directly.
- `\Lunar\Core\` contains everything the core package previously exposed.
- Sibling packages (admin, stripe, paypal, opayo, meilisearch, search, table-rate-shipping) keep their existing roots but update their `use` statements and any string references.
- The upgrade package (TODO) ships Rector rules that rewrite `Lunar\Foo` → `Lunar\Core\Foo` across user code, config, and published views.

Class string references in config files, morph maps, and Filament resource registrations are migrated in the same pass.

## Alternatives considered

- **Leave it as-is and namespace new ecosystem packages elsewhere (e.g. `\LunarPhp\`).** Rejected — fragments the brand across two PHP namespaces and does not solve the coupling problem for core itself.
- **Move only new code into `\Lunar\Core\` and leave existing classes at `\Lunar\`.** Rejected — leaves a permanent split where the same package exposes two roots, and means the v1 → v2 upgrade has to teach users two rules instead of one.
- **Use sub-namespaces like `\Lunar\Cart\`, `\Lunar\Order\` without a `Core` umbrella.** Rejected — still occupies the ecosystem root with engine-specific classes; doesn't free the root for the admin/filament split.

## Migration impact

- **Public contract surface**: every public class, interface, enum, exception, and trait in core moves. This is a wholesale breaking change for v2.
- **Database**: morph map entries and any class strings stored in the database (e.g. polymorphic `*_type` columns, discount/condition class names, scheduled job payloads) need rewriting. The migration needs to enumerate which tables store class strings.
- **Config**: `config/lunar/*` references and any user-published copies need updating.
- **Upgrade package**: Rector rules for `Lunar\X` → `Lunar\Core\X`, plus a data migration step for class strings in user databases.
- **Translations**: no direct impact on the 16 locale files, but any translation keys that embed class names (if any) need auditing.
- **Filament / admin**: the admin package's `use` statements and any class-string registrations (resources, pages, widgets) update accordingly. Coordinate with the Filament v5 upgrade and the `lunarphp/filament` extraction so we only churn the admin once.
- **Docs**: every code example in the docs site needs rewriting; we should script this rather than do it by hand.

## Open questions

- Should the move happen in a single PR or be staged behind a `class_alias` compatibility layer for one minor v2 release? (Recommendation: single PR — v2 is already a breaking release and aliases would leak the old root.)
- Do we publish the Rector rules as a standalone package or as part of the Upgrade package? Ties into [[0000-template]] until the Upgrade spec exists.
- What is the policy for third-party packages — do we ask them to also vacate `\Lunar\` (other than `\Lunar\Core\`) and reserve the root entirely for first-party use?
- Are there any class strings persisted in user data that we cannot detect from our own schema (e.g. user-defined discount conditions)? If so, the upgrade tool needs a manual step.

## References

- `packages/lunar/packages/core/composer.json` — current autoload mapping
- TODO items: "Add an Upgrade package for those migrating from v1.x (using Rector)", "Move core Filament e-commerce components to a new `lunarphp/filament` package", "Filament v5 upgrade"
