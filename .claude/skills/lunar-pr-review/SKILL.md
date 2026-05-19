---
name: lunar-pr-review
description: Review a Lunar pull request or branch against the 1.x main branch. Activates on /lunar:pr-review, or when the user asks to "review this PR/branch/diff" inside the Lunar monorepo. Checks translation completeness across 16 locales, missing tests/factories, migration safety, Filament contract usage, PHP conventions, and breaking-change risk on the public contract surface.
metadata:
  audience: lunarphp/lunar contributors
---

# Lunar PR Review

## Overview

Lunar is a Laravel-based headless commerce package distributed as a monorepo (`packages/core`, `packages/admin`, plus payment/search adapters). PRs target the `1.x` branch. This skill runs a deterministic review pass tuned to the patterns that actually exist in this codebase, surfaces findings as `path:line — issue — fix`, and groups them by severity. It does not make changes.

## When to Activate

- Slash command `/lunar:pr-review` (with optional base branch arg).
- Natural-language requests: "review this PR", "review the branch", "review the diff", "check my changes before I push".
- Skip if the user wants a brand-new implementation — this skill is read-only review.

## Scope

- **In scope**: changed files between `HEAD` and `origin/1.x` (or a user-supplied base).
- **Out of scope**: unchanged files, generated docs (`packages/*/resources/views/vendor`), `vendor/`, lock files beyond noting they changed.

## Workflow

1. **Determine base branch.** Default `1.x`. If the user passed an arg (e.g. `/lunar:pr-review main`), use that. Run `git fetch origin <base> --quiet`.
2. **Collect the diff.**
   - Files: `git diff --name-status origin/<base>...HEAD`
   - Commits: `git log --oneline origin/<base>..HEAD`
   - If empty, report "no changes vs `<base>`" and stop.
3. **Classify** each changed file into buckets: `migrations`, `translations`, `models`, `filament`, `observers/events`, `tests`, `composer`, `factories`, `other`.
4. **Run category checks** (see below). Build a findings list with `severity`, `path`, `line` (when known), `issue`, `suggested fix`.
5. **Render report** grouped as **Blockers** → **Should fix** → **Nits**. End with a "Run before merge" footer.

## Categories

Each check below states what to look for and the severity to assign. See `references/checklist.md` for full rationale and good/bad examples.

### Translations (Blocker on missing, Should-fix on stale)

Run `php .claude/skills/lunar-pr-review/scripts/missing-translations.php` from the repo root. The script flattens nested array keys to dot notation and diffs `en/` against `ar bg de es fa fr hr hu mn nl pl pt_BR ro tr vi` for every `packages/*/resources/lang/<locale>/<file>.php`.

- `[missing] …`  → key exists in `en/` but not in target locale → **Blocker** if the PR added the key; **Should-fix** if pre-existing.
- `[stale] …`    → key in target locale no longer in `en/` → **Should-fix**.
- `[missing-file] …` → whole file absent in target locale → **Should-fix**.

Also scan added strings in PHP/Blade for hardcoded user-facing English that should be `__('lunarpanel::…')`.

### Tests (Pest) — Should-fix

For each newly added class under `packages/*/src/`:
- Models, services, actions, observers, jobs, and events should have a matching Pest test under `tests/{core,admin,opayo,paypal,shipping,stripe,search}/`.
- New `Eloquent` models additionally require a factory under `packages/*/database/factories/`.
- Use `php artisan make:test --pest <Name>Test` (do not write to `tests/Feature/` paths directly).

### Migrations — Blocker

For each migration in `packages/*/database/migrations/`:
- Must `extend Lunar\Base\Migration` (not `Illuminate\Database\Migrations\Migration` directly).
- Must use `$this->prefix` when referencing table names (`Schema::table($this->prefix.'orders', …)`).
- Must implement `down()` symmetrically — every `Schema::create` / column-add in `up()` is reversed.
- Foreign keys use `foreignId(...)->constrained()` with explicit `onDelete`/`onUpdate` where the parent model uses soft deletes.
- Destructive column drops on existing tables require a justification comment or a paired data migration.
- File name follows `YYYY_MM_DD_HHMMSS_<verb>_<thing>.php`.

### Filament resources & admin — Should-fix

For files under `packages/admin/src/Filament/Resources/`:
- `protected static ?string $model = …Contract::class;` — must reference an interface from `Lunar\Models\Contracts\*`, not a concrete model.
- Labels: `getLabel`, `getPluralLabel`, `getNavigationGroup`, form/column labels must come from `__('lunarpanel::…')`; flag any hardcoded English strings.
- New resources set `protected static ?string $permission`.
- Navigation icons resolved via `FilamentIcon::resolve('lunar::…')`.

### Model contracts (type-hinting) — Blocker

Eloquent models in `packages/*/src/Models/` are bound to interfaces in `Lunar\Models\Contracts\*` via the model manifest, so consumers can swap in their own subclass. Code that type-hints the concrete model defeats that extension point.

Anywhere outside the model class itself and its factory/seeder, type hints, return types, property types, and PHPDoc references must use the contract, not the concrete model:

- Method parameters & return types: `public function handle(CartContract $cart, Closure $next): CartContract`.
- Promoted/declared properties: `protected ?OrderContract $order = null;`.
- PHPDoc: `@param Closure(OrderContract): mixed $next`, `@return Collection<int, ProductContract>`.
- DI / container resolution: `app(CartContract::class)`, not `app(Cart::class)` or `new Cart`.

Exemptions (concrete class is correct here):
- The model class itself, its relations, scopes, and casts.
- Factories, seeders, and migrations.
- Tests creating fixtures (`Product::factory()->create()`).
- `instanceof` checks against the concrete class only when there is a documented reason (otherwise prefer the contract).

Flag as **Blocker** in new/changed code: `use Lunar\Models\Foo;` followed by a type hint `Foo $foo` in a service, action, manager, pipeline, observer, listener, event, or job. Suggest the fix as `use Lunar\Models\Contracts\Foo as FooContract;` and rename the hint to `FooContract`.

### PHP conventions — Should-fix / Nit

Per repo `CLAUDE.md`:
- Constructor property promotion required for public constructor params; flag empty zero-param `__construct()`.
- Explicit return types and typed parameters on every method.
- Curly braces around every control-structure body, even one-liners.
- Prefer PHPDoc blocks over inline comments; array shape types in PHPDoc.
- Never call `env()` outside `config/`.
- Enum case keys in `TitleCase`.

### Static analysis & style — Nit (reminder)

- If changed files fall inside paths excluded by `phpstan.neon.dist`, note that PHPStan will not catch issues there.
- Remind the user to run `vendor/bin/pint --dirty --format agent` before pushing.

### Composer / dependencies — Blocker (requires user confirmation)

Any diff in `composer.json` or any `packages/*/composer.json`:
- Flag added/removed/upgraded dependencies as a Blocker that needs explicit user sign-off per `CLAUDE.md`.
- Note PHP version, Laravel version, or Filament version bumps separately — they affect downstream consumers.

### Channel scoping — Blocker

Lunar is multi-channel. Models using `Lunar\Base\Traits\HasChannels` (`Product`, `Collection`, `Discount`, …) expose `scopeChannel()` for filtering.

- New queries on these models in admin lists, storefront-facing endpoints, or scheduled jobs should call `->channel($channel)` (or be explicitly justified as cross-channel).
- New columns/relations on a channelled model that drive visibility need the channel scope wired in, not just the column added.

### Reuse existing scopes & traits — Should-fix

Before reviewing any new Eloquent query, check whether the related trait/model already exposes a scope for the same condition. Hand-rolled `->where(...)->orWhere(...)` chains that duplicate a trait scope are a Should-fix.

- `Lunar\Base\Traits\HasCustomerGroups` exposes a scope for customer-group eligibility (enabled/visible/`starts_at`/`ends_at` windowing). New `->customerGroups()->where('enabled', true)…` chains in resources, widgets, validators, or pipelines should use that scope.
- `Lunar\Base\Traits\HasChannels` — see Channel scoping above; use `->channel(...)`, not `->channels()->where('enabled', true)…`.
- `Lunar\Base\Traits\HasUrls` — use the `default()` / active scope rather than re-checking columns inline.
- Status enums on `Product`, `Order`, etc. — prefer `->whereIn('status', SomeStatus::active())` or the existing helper over string-literal comparisons.

When flagging, name the specific trait/scope the author should use, not just "use a scope". If the scope doesn't exist yet but the same chain is repeated 2+ times in the diff, suggest adding it to the trait.

### Control-flow simplification — Nit (Should-fix when stacked)

Lunar's Filament resources and validators have accumulated long ladders of `if ($x) { return true; } if ($y) { return true; }` inside closures (notably `Shout::make(...)->hidden(fn ...)`). Reviewers consistently push back on these — flag them.

- Multiple sequential `if (cond) { return $literal; }` returning the same literal collapse into one `if (a || b || c) { return $literal; }` or a single boolean expression on the return.
- A trailing `if (! $x) { return true; } return (bool) $x;` is just `return ! $x;` (or the equivalent expression).
- Guard clauses are fine and preferred over nested `if/else`, but each one should branch to a *different* outcome — back-to-back guards returning the same value are noise.
- Closures that grow past ~5 statements doing query composition should be extracted to a named private method or, better, an Eloquent scope (see above).

Treat a single instance as a Nit; flag as Should-fix when the same closure stacks 3+ early returns or when the pattern repeats across sibling components in the same file.

### Money & price handling — Blocker

Money in Lunar lives in `Lunar\DataTypes\Price` (integer minor units + `Currency`) and the `Lunar\Base\Casts\Price` cast.

- Flag new monetary columns as `decimal`/`float` — they should be `unsignedInteger`/`bigInteger` and cast through `Price`.
- Flag float arithmetic on money: `* 0.01`, `(float)`, `round()` on raw totals, `+`/`-`/`*` between mixed-currency `Price` instances.
- New tax/discount/shipping totals must go through the existing pipeline calculation, not be set directly on the model.

### Search indexing (Scout/Meilisearch) — Should-fix

Search documents are built by `packages/core/src/Search/*Indexer.php` (`ProductIndexer`, `CollectionIndexer`, `CustomerIndexer`, `OrderIndexer`, `ProductOptionIndexer`).

- Adding a queryable/filterable attribute to an indexed model? The corresponding `*Indexer` must include it in the document.
- Removing an attribute? Note that indexed documents are now stale and need a re-index step (`php artisan scout:import "<Model>"`).
- New filterable/sortable attributes likely need the Meilisearch `filterableAttributes` / `sortableAttributes` config updated.

### Pipelines — Blocker on calculation paths

Cart, cart line, cart prune, and order pipelines live under `packages/core/src/Pipelines/`. Lunar resolves them from config so consumers can extend or replace them.

- Adding a new field to `CartLine`/`Cart`/`Order` that affects price, tax, shipping, or eligibility means adding (or extending) the relevant pipeline stage — not just persisting the field.
- New pipeline stages must be registered in `config/lunar/cart.php` / `config/lunar/orders.php` and have a Pest test.
- Reordering existing stages is a behavior change — flag loudly.

### Event payload stability — Blocker

Event classes under `packages/*/src/Events/` and `packages/*/src/Base/Events/` are public API on `1.x`. Listeners depend on the constructor signature and public properties.

- Renaming/removing/retyping constructor params or public properties on an existing event is breaking.
- Adding new optional params at the end of a constructor is acceptable; new public properties are acceptable.
- New events that should be dispatched from observers/actions but aren't — flag as Should-fix.

### Public API surface — Blocker

`1.x` is a stable line. The following count as breaking changes and must be flagged loudly:
- Any change in `packages/*/src/Models/Contracts/` (added/removed/renamed methods, changed signatures).
- Public method signature changes on classes that implement a `Contracts/*` interface.
- Removed or renamed events under `packages/*/src/Base/Events/` or `packages/*/src/Events/`.
- Removed config keys in `packages/*/config/*.php`.
- Migrations that rename existing tables/columns without a backwards-compatible accessor.

## Output Format

```
# Lunar PR Review — <branch> vs <base>

<N commits, M files changed>

## Blockers (must fix before merge)
- `path/to/file.php:L42` — <issue> — <suggested fix>
- …

## Should fix
- `path/to/file.php:L11` — <issue> — <suggested fix>
- …

## Nits
- `path/to/file.php` — <issue>
- …

## Run before merge
- `vendor/bin/pint --dirty --format agent`
- `php artisan test --compact`
- `vendor/bin/phpstan analyse`
```

If there are no findings in a section, omit it. If there are zero findings overall, output a single line: `No findings vs <base> across <N> files.`

## Do / Don't

Do:
- Run the translations script — do not eyeball 16 locales.
- Quote line numbers from `git diff --unified=0` when available so the user can jump to them.
- Recommend `php artisan make:*` commands for any missing scaffolding rather than describing hand-rolled code.

Don't:
- Don't edit any files.
- Don't push, force-push, rebase, or reset.
- Don't run `composer update`, `composer require`, `npm install`, or migrations.
- Don't claim a file is fine without actually reading the changed hunks.
- Don't duplicate findings — each `path:line` appears once at its highest severity.

## References

- `references/checklist.md` — full rubric with good/bad examples drawn from the codebase. Load when a category needs deeper explanation.
- `scripts/missing-translations.php` — run from repo root, exits 0, prints `[missing]`/`[stale]`/`[missing-file]` lines + summary.
