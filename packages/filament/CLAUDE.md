# lunarphp/filament

A Filament v5 bridge package for Lunar. Ships the reusable building blocks (entity selectors, schemas, tables, infolists, relation managers, actions, global-search descriptors, dashboard widgets, attribute field types) that any Filament v5 panel can drop in.

Consumed by `lunarphp/admin` (Lunar's turnkey panel) and by downstream developers wiring Lunar into their own panels. **Everything outside `Concerns/` and `Support/` is public contract — breaking it requires a spec and a Rector rule in `lunarphp/upgrade`.**

This package lives in the [Lunar monorepo](https://github.com/lunarphp/lunar) during v2 development and extracts into its own repository (`lunarphp/filament`) at the v2.0.0 stable cut. Treat it as already split: don't reach into sibling packages except through their public contracts.

## Package boundaries

| Lives here | Lives in `lunarphp/core` | Lives in `lunarphp/admin` |
| --- | --- | --- |
| Filament `Action`/`BulkAction` wrappers (UI, modal schema, notifications) | The business logic those actions delegate to (`Lunar\Core\Actions\…`) | The turnkey panel shell (navigation, branding, auth, dashboard) |
| Schema / table / infolist classes per model | Eloquent models, value objects, payment drivers | The default `BaseResource` and page templates |
| Entity selectors, attribute field types | Field-type contracts (`Text`, `TranslatedText`, …) | Resource subclasses that compose bridge schemas |
| `GlobalSearchDescriptor` classes | — | The five Lunar resources that opt into descriptors |

When in doubt: **a Filament class shouldn't own commerce rules.** If you find yourself writing refund maths, status transition logic, stock arithmetic, or pricing inside an `Action`, lift it into `Lunar\Core\Actions\…` and call it from here. See `specs/0009-filament-actions-and-global-search.md` for the criteria.

## Namespace and structure

Namespace: `Lunar\Filament\…` (PSR-4 → `src/`).

```
src/
  Actions/         — Filament Action / BulkAction wrappers grouped by subject (Orders, Products, Collections, Support)
  FieldTypes/      — attribute-system field-type renderers (TextField, TranslatedText, Dropdown, …)
  Forms/           — selectors and generic form components, plus shared concerns and the RecordSearch backend
  GlobalSearch/    — per-model GlobalSearchDescriptor subclasses + the HasLunarGlobalSearch consumer trait
  Infolists/       — infolist entries (Timeline, Tags, Transaction, …)
  RelationManagers/ — per-model relation managers grouped by parent
  Schemas/         — {Model}Form / {Model}Infolist classes
  Tables/          — {Model}Table classes (Tables/Columns/ holds reusable columns)
  Widgets/         — dashboard widgets
  Support/         — public helpers + Concerns/ traits
  Synthesizers/    — Livewire synthesizers
  Events/          — events the bridge dispatches
```

## Conventions

- **Action naming**: `{Verb}{Subject}Action` for single, `{Verb}{Subject}BulkAction` for bulk (`PublishProductsBulkAction` — plural subject for bulk). One class per action; no inline `Action::make(…)` closures in pages or resources.
- **Schema naming**: `{Model}Form` / `{Model}Infolist` / `{Model}Table` under `Schemas\{Model}` / `Tables\{Model}`. Each exposes `static configure(Schema|Table)` plus granular `getXxxFormComponent()` / `getXxxTableColumn()` helpers — never make a schema/table class force-include unrelated fields.
- **Customisation surface**: every publicly-callable schema/table/action/descriptor must support the three documented strategies — extension hooks (`LunarFilament::extensions([...])`), subclass-and-rebind (the container resolves it), and (where applicable) publish stubs. Pages/widgets that already use the `Resolver` facade keep doing so.
- **Selectors**: extend `Lunar\Filament\Forms\Components\…Select`. They share one Scout-aware search backend via `Lunar\Filament\Forms\Components\Support\RecordSearch`. Never re-implement search per-selector.
- **PHP**: 8.3+. Curly braces for all control flow. Constructor property promotion. Explicit return types and parameter types. Array-shape PHPDoc on public surface so phpstan can verify call sites without baseline noise.

## Public contract surface

Anything reachable from outside `Concerns/`, `Support/`, and internal namespaces is contract.

- **Net-additive changes** (new selectors, new action classes, new schema methods, new descriptors) ship without a spec.
- **Renames, removals, signature changes, behavioural shifts** require a spec under `specs/NNNN-…md` in the monorepo and a corresponding Rector rule in `lunarphp/upgrade` so consumers on the previous version can auto-migrate.
- A deprecated class becomes a thin shim that forwards to the replacement, marked `@deprecated since vX — use Y`. It stays for one minor cycle before removal.
- Adding a `protected static` property on a trait that's also redeclared by consumers is a fatal — declare the property only on consumer classes and document the requirement in the trait's docblock (see `Concerns/HasLunarGlobalSearch.php` for the precedent).

## Documentation route

When you add or change a public surface, **the README is the canonical user-facing reference.** Every PR that touches public surface must update it in lockstep with the code.

1. **`README.md`** — primary contract documentation.
   - "What's in the box" table: add a row for any new surface category.
   - The relevant section (`## Entity selectors`, `## Actions`, `## Global search`, `## Dashboard widgets`, …) gets an entry for the new class, with at least one usage example.
   - The Configuration / Translations / Customisation sections update when new config keys, lang files, or strategies land.
   - Match the existing tone: terse, code-first, examples drawn from the actual class. Don't repeat what a `@phpdoc` block already says — link the reader to the class.

2. **`IDEAS.md`** — backlog of future additions.
   - When you ship something that was an idea, append `_(Shipped in spec NNNN.)_` to the item; don't delete it.
   - When a spec is suggested as "next", refresh the "Suggested next spec" footer.

3. **Release notes come from PR titles** — there is no per-package `CHANGELOG.md` (features regularly span sub-packages, so a single package's file told a partial story). Give every PR a conventional-commit title scoped to the package(s) it touches (`feat(filament): …`, `fix(core,filament): …`); release notes are compiled from merged PR titles at each release. Call out deprecations and behavioural changes prominently in the PR description — that text is what the release notes link to.

4. **Translations** — see below; lang keys are part of the public surface.

5. **PHPDoc on every public class.** One-paragraph what+why at the class level; `@param` / `@return` array shapes on methods that take/return structured arrays. The README points readers at classes; the PHPDoc tells them what each does.

6. **Spec link.** If the change implements a spec, mention the spec slug in the relevant README section or in a class-level docblock so future readers can trace the decision.

If a change touches public surface and the README diff is empty in the PR, the PR is incomplete.

## Translations

Lang files live under `resources/lang/{locale}/` across **16 locales**: `ar`, `bg`, `de`, `en`, `es`, `fa`, `fr`, `hr`, `hu`, `mn`, `nl`, `pl`, `pt_BR`, `ro`, `tr`, `vi`.

- Lang keys are public contract — renaming a key requires a Rector rule.
- English first. Mirror to the other 15 locales with the English value as a placeholder. The translation community fixes the placeholders downstream.
- Lang namespaces: `lunar-filament::{file}.…`. Files in use: `actions.php`, `address.php`, `attribute.php`, `brand.php`, `collection.php`, `components.php`, `currency.php`, `customer.php`, `discount.php`, `fieldtypes.php`, `global-search.php`, `order.php`, `product.php`, `producttype.php`, `productvariant.php`, `widgets.php`, plus the smaller per-model files.
- Never inline a user-facing string — always go via `__('lunar-filament::…')`. Tests don't catch missing translations, only missing keys when called.

## Tests

The package's tests live in the monorepo at `tests/filament/` (Pest, Orchestra Testbench).

- `vendor/bin/pest --testsuite=filament` from the monorepo root.
- Filter by file: `vendor/bin/pest tests/filament/Unit/Forms/Components/ProductSelectTest.php`.
- Filter by name: `vendor/bin/pest --testsuite=filament --filter='renders the search dropdown'`.
- Use factories from `lunarphp/core`'s `database/factories`. Don't invent ad-hoc test data when a factory state already covers it.
- For Filament action tests, assert the modal schema, the visibility predicate, and that the core action is called with the right arguments — leave the underlying behaviour to the core action's own tests under `tests/core/Unit/Actions/`.

After the v2.0.0 split, tests move into the standalone repo.

## Static analysis and formatting

- **PHPStan** (Larastan, level 0): `vendor/bin/phpstan analyse --no-progress`. Must pass. No `@phpstan-ignore` lines — fix the underlying type issue.
- **Pint**: `vendor/bin/pint --dirty --format agent` before finalizing a change. Don't run `--test` — just fix.

## Spec-driven development

Non-trivial additions go through `specs/NNNN-{slug}.md` in the monorepo. The spec lands before implementation. Already-shipped specs that shaped this package:

- `0006` — bridge package extraction and install model.
- `0007` — page-extension traits inlined into base page classes.
- `0008` — reusable entity-selector components + `RecordSearch` consolidation.
- `0009` — Filament-native verbs (actions library) and discoverability (global search).

Read these before designing a change in the area they cover — the rationale will save you re-deriving boundaries.

## Customisation strategies

Three supported ways for consumers to bend bridge behaviour, in escalating order:

1. **Extension hooks** — `LunarFilament::extensions([SomeClass::class => new class { public function configureForm($form) { … } }])`. Additive, runtime, ideal for "add a field" / "tweak a label" / "wrap an action".
2. **Subclass and rebind** — extend the bridge class, bind the subclass in the container; `::make()` calls resolve through the container. Use when behaviour changes are too structural for hooks.
3. **Publish stubs** — `php artisan vendor:publish --tag=lunar-filament.schemas`. Copies into `app/Filament/…` (configurable via `lunar.filament.publish_path`). One-way door — the published copy no longer receives upstream improvements. Prefer the first two strategies unless the consumer genuinely owns the file.

When designing a new public class, work through all three: hooks should fire at sensible points, the class should be subclass-friendly (final methods only when invariants demand it), and stub-publishable surfaces should be self-contained.
