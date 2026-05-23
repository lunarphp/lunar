# 0007 — Inline page-extension traits into base page classes

- Status: implemented
- Author: Glenn Jacobs
- Created: 2026-05-22
- TODO item: "Pages refactor (spec 0005 follow-up)"

## Problem

Spec [[0005-filament-v5-schemas-refactor]] dropped the `Extends*` wrapper traits on **resources** — each `{Resource}.php` now overrides Filament's native `form()` / `table()` / `infolist()` directly and calls into the split-class schemas. The same wrapper-trait pattern still exists for **pages**: ten traits under `packages/admin/src/Support/Pages/Concerns/` (`ExtendsForms`, `ExtendsTables`, `ExtendsInfolist`, `ExtendsHeaderActions`, `ExtendsHeaderWidgets`, `ExtendsFooterWidgets`, `ExtendsHeadings`, `ExtendsTabs`, `ExtendsTablePagination`, `ExtendsFormActions`) that the five base page classes (`BaseListRecords`, `BaseCreateRecord`, `BaseEditRecord`, `BaseViewRecord`, `BaseManageRelatedRecords`) mix in.

Each trait follows the same shape:

```php
trait ExtendsHeaderActions
{
    protected function getDefaultHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return $this->callLunarHook('headerActions', $this->getDefaultHeaderActions());
    }
}
```

A `getDefault*` slot for the page author to override, wrapped by an `Extends*`/`get*` method that fires the Lunar extension hook. The slot name (`getDefaultHeaderActions`) is Lunar-bespoke; the wrapper name (`getHeaderActions`) is what Filament itself calls.

The cost of leaving this in place:

- **Inconsistent with the resources playbook.** Spec 0005's whole motivation was matching Filament v5's generator conventions; the page side still doesn't.
- **Two indirections to read** when tracing a header action / form schema / pagination query — the trait, then the base page, then the concrete page.
- **`getDefault*` is unidiomatic.** When a downstream developer overrides `getDefaultHeaderActions()`, IDE/Filament docs don't recognise the method. Overriding `getHeaderActions()` directly (Filament's native slot) would, but doing so silently skips the Lunar hook.
- **`ExtendsTablePagination`** is the worst case: it overrides Filament's `paginateTableQuery()` via a `getDefaultPaginationQuery()` slot that delegates back to `parent::paginateTableQuery()` — three layers of indirection for a single hook call.

Spec 0005 explicitly deferred this work: *"Resource migration preserved their hook contract, so the split-class playbook can be applied later when a downstream need surfaces, without blocking v2."* This spec picks it up so v2 ships with a uniform shape across resources and pages.

## Proposal

Inline the ten `Extends*` page-concern traits into the five base page classes that use them, rename the `getDefault*` slots to Filament's native method names, and rebuild the Lunar extension surface at the new seam.

### Mechanical changes

For each of the five base page classes:

- Remove the `Extends*` trait imports and `use` lines.
- Inline each trait's body into the class. The `get*` wrapper becomes the class's override of Filament's native method; the `getDefault*` slot is renamed to the matching Filament name and made `protected` (so subclasses can still override).

Concretely:

| Trait                       | Class(es) it lives in                                           | Slot rename                                                            |
| --------------------------- | --------------------------------------------------------------- | ---------------------------------------------------------------------- |
| `ExtendsForms`              | `BaseCreateRecord`, `BaseEditRecord`                            | `getDefaultForm` → `getDefaultForm` (stays — Filament has no native `form` slot on page subclasses; the wrapper override of `form()` stays) |
| `ExtendsTables`             | `BaseManageRelatedRecords`                                      | `getDefaultTable` → `getDefaultTable` (same — `table()` override stays) |
| `ExtendsInfolist`           | `BaseViewRecord`                                                | `getDefaultInfolist` → `getDefaultInfolist`                            |
| `ExtendsHeaderActions`      | `BaseListRecords`, `BaseCreateRecord`, `BaseEditRecord`, `BaseViewRecord`, `BaseManageRelatedRecords` | `getDefaultHeaderActions` → `getDefaultHeaderActions`                  |
| `ExtendsHeaderWidgets`      | all five                                                        | `getDefaultHeaderWidgets` → `getDefaultHeaderWidgets`                  |
| `ExtendsFooterWidgets`      | all five                                                        | `getDefaultFooterWidgets` → `getDefaultFooterWidgets`                  |
| `ExtendsHeadings`           | all five                                                        | `getDefaultHeading` / `getDefaultSubheading` stay                      |
| `ExtendsTabs`               | `BaseListRecords`                                               | `getDefaultTabs` stays                                                 |
| `ExtendsTablePagination`    | `BaseListRecords`                                               | `getDefaultPaginationQuery` collapses; `paginateTableQuery` becomes the single override |
| `ExtendsFormActions`        | `BaseCreateRecord`, `BaseEditRecord`                            | `getDefaultFormActions` stays                                          |

The `getDefault*` names stay for slots whose Filament-native name **is** the wrapper (e.g. `getHeaderActions`). Renaming the slot to the same name as the wrapper isn't possible. So the choice is: keep `getDefault*` (current shape) or invert — make the slot Filament's native name and put the hook in a different method. After weighing both:

**Decision: keep `getDefault*` as the slot name.** Inlining alone removes one layer of indirection (the trait) without disturbing the slot name. The benefit of "matches Filament's docs verbatim" is real but small — page subclasses are admin-internal, not the public extension surface. Apps that override a slot today keep working.

### Extension hook target

`LunarPanel::extensions([…])` (now backed by `LunarFilament::extensions([…])` per spec 0006) currently fires hooks against the *page class*:

```php
LunarPanel::extensions([
    EditBrand::class => SomeHeaderActionsExtension::class,
]);
```

with the extension exposing `headerActions(array $actions): array`. That stays as-is. The wrapper method on the base page (`getHeaderActions`) is what calls `callLunarHook('headerActions', …)`; inlining the trait body doesn't change the registration target or method names.

### `ExtendsTablePagination` cleanup

The current shape:

```php
protected function getDefaultPaginationQuery(Builder $query): Paginator|CursorPaginator
{
    return parent::paginateTableQuery($query);
}

protected function paginateTableQuery(Builder $query): Paginator|CursorPaginator
{
    $query = $this->callLunarHook('paginateTableQuery', $query, $this->getTableRecordsPerPage());

    return $query instanceof Builder ? $this->getDefaultPaginationQuery($query) : $query;
}
```

After inlining into `BaseListRecords`, drop `getDefaultPaginationQuery` (it has one caller, returns `parent::paginateTableQuery($query)`). The result:

```php
protected function paginateTableQuery(Builder $query): Paginator|CursorPaginator
{
    $result = $this->callLunarHook('paginateTableQuery', $query, $this->getTableRecordsPerPage());

    return $result instanceof Builder ? parent::paginateTableQuery($result) : $result;
}
```

One method instead of two.

### `ExtendsForms::getDefaultForm` and the resource delegate

`BaseCreateRecord::getDefaultForm()` currently does `return static::getResource()::form($schema);` — i.e. it pulls the form definition from the resource. This stays. The inlining moves the line into `BaseCreateRecord` itself instead of the trait.

### Trait file deletion

After the inlining, the ten trait files under `packages/admin/src/Support/Pages/Concerns/` are deleted. Anyone who imported them directly (rare — they were `use`d on base pages, not by consumers) needs to update; Rector covers the namespace rewrite to "removed" by mapping to a sentinel that produces a clear error.

Actually no — directly removed namespaces can't be Rector-fixed to a sensible replacement. Leave one-line shim traits in place for v2 (each trait re-exports the inlined behaviour from the base class via `use BaseListRecords::getHeaderActions` — not possible cleanly across PHP traits). Simpler: keep the trait files as **empty deprecated stubs** that `@trigger_error` with a `@deprecated` notice; remove in v3.

**Decision:** delete the trait files outright. Internal code consuming them is updated in this spec; external code consuming them is rare and the resulting "class not found" error is acceptable for a v2 breaking change. The upgrade package's Rector rules document the rename for anyone who hits it.

### Bridge vs admin

Spec 0006 introduced a parallel `Lunar\Filament\Support\Concerns\RelationManagers\{ExtendsForms,ExtendsTables}` pair on the bridge for relation managers. Those stay — relation managers are a bridge concern (any Filament panel using bridge schemas wants relation-manager hook plumbing), pages are an admin-shell concern (the base page classes themselves live in admin).

This spec does not move any code to the bridge. It rearranges admin-internal code only.

## Alternatives considered

- **Do nothing.** Rejected — spec 0005 set the precedent that wrapper traits get inlined; leaving the page side half-done is exactly the kind of inconsistency v2 is supposed to close.

- **Rename the slot to Filament's native method name (e.g. `getDefaultHeaderActions` → `getHeaderActions`) and put the hook in a `*WithExtensions` method.** Rejected — IDE / docs alignment is a smaller win than the cost of breaking every consumer override of `getDefaultHeaderActions`. The current names are bespoke but stable; renaming them now creates noise for downstream apps that have already overridden them.

- **Push the hook plumbing into the bridge.** Rejected — the base page classes themselves are admin-shell concepts (`BaseListRecords` knows about scout search, `BaseEditRecord` knows about `sync_with_search`). The bridge has no pages; moving the hook plumbing to a bridge trait that admin then `use`s would re-introduce the indirection this spec is trying to remove.

- **Replace the whole hook system on pages with Filament's own `boot*` lifecycle.** Rejected — spec 0005 deliberately kept `LunarPanel::extensions([…])` as the canonical surface for plug-in authors. Carving out a different system for pages would split the contract.

## Migration impact

- **Database**: none.
- **Public contract surface**:
  - Deleting the ten `Lunar\Admin\Support\Pages\Concerns\Extends*` traits is a breaking change for any downstream code that imported them directly. Rare; Rector emits a friendly error pointing at the inlined behaviour on the base page.
  - The `getDefault*` slot names and the `LunarPanel::extensions([…])` registration target (page class) **do not change**. Existing extension classes keep working as-is.
- **Upgrade path for v1.x consumers**: lands in [[0001-upgrade-package]]'s set list alongside the spec-0005/0006 rewrites. The upgrade command rewrites the trait imports to delete them; the user's class continues to compile because the base page absorbs the behaviour.
- **Translations**: none.
- **Filament / admin**: this _is_ the admin change. No bridge impact.
- **Plugins**: third-party plugins that **only** register extensions via `LunarPanel::extensions([…])` see no change. Plugins that subclassed a base page and `use`d an `Extends*` trait directly (instead of inheriting it through the base) need to drop the `use` line — the trait no longer exists, but the base class already provides the override slot.

## Open questions

- **`getDefault*` slot names** — kept as-is in this spec. If we later decide to rename them to Filament's native equivalents (`getHeaderActions` → `getHeaderActions`, requiring a different name for the wrapper), that's a separate breaking change worth its own spec.
- **Deprecation period** — this spec proposes outright deletion in v2 rather than a deprecated-trait + removed-in-v3 cycle. Worth a quick gut-check against how many real-world consumers import the traits directly. If the answer is "more than a handful", switch to deprecated stubs.

## References

- [[0005-filament-v5-schemas-refactor]] (completed) — established the inline-the-wrapper-trait playbook on resources, and explicitly deferred this spec's work
- [[0006-filament-bridge-package]] — moves the extension registry into the bridge; this spec's hook calls go through `LunarFilament::callHook` transitively
- [[0001-upgrade-package]] (completed) — Rector host for the trait-import removals
- `packages/lunar/packages/admin/src/Support/Pages/Concerns/` — the ten traits to inline
- `packages/lunar/packages/admin/src/Support/Pages/Base*.php` — the five base page classes that absorb them
- Filament v5 pages overview: https://filamentphp.com/docs/5.x/resources/pages
