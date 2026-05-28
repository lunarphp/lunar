# 0020 — Remove GetCandy migration command

- Status: completed
- Author: Glenn Jacobs
- Created: 2026-05-28
- TODO item: (not previously tracked)

## Problem

`Lunar\Core\Console\Commands\MigrateGetCandy` (`lunar:migrate:getcandy`) exists to walk a GetCandy install forward into Lunar — renaming `getcandy_*` tables, rewriting polymorphic `GetCandy\…` morph types into `Lunar\…`, and merging `attribute_data` JSON blobs. It was authored against the v1 schema and is registered in `Lunar\Core\LunarServiceProvider`. It is also carried in `Lunar\Upgrade\Rector\LunarSetList::V1_TO_V2_CLASS_RENAMES` so v1 → v2 consumers keep the class-string alias.

GetCandy was renamed to Lunar at Lunar v0.x. The supported upgrade path into v2 is v1.x → v2.x via the `lunarphp/upgrade` package (spec 0001) — anyone still on GetCandy would land on v1.x first and run the GetCandy migration there. Keeping the command in v2 ships dead code that:

- references column shapes (`brand`, `getcandy_products`) that no longer exist in the v2 flat baseline (spec 0003)
- assumes a polymorphic `attributes.type` column that the attribute redesign (spec 0019) has removed
- adds a published Artisan verb (`lunar:migrate:getcandy`) to v2's public CLI surface that cannot succeed against a v2 schema

## Proposal

Delete the GetCandy migration command and its registration. Nothing replaces it.

1. Delete `packages/core/src/Console/Commands/MigrateGetCandy.php`.
2. In `packages/core/src/LunarServiceProvider.php`, remove the `use Lunar\Core\Console\Commands\MigrateGetCandy;` import and the `MigrateGetCandy::class` entry from the `$this->commands([...])` registration.
3. In `packages/upgrade/src/Rector/LunarSetList.php`, remove the `'Lunar\\Console\\Commands\\MigrateGetCandy' => 'Lunar\\Core\\Console\\Commands\\MigrateGetCandy'` rename entry.
4. Grep the monorepo for any remaining `MigrateGetCandy`, `migrate:getcandy`, or `GetCandy` string references and remove them (docs, stubs, tests). At spec time the only hits are the three files above.

No new code, no replacement command, no shim. The Artisan verb `lunar:migrate:getcandy` is gone in v2.

## Alternatives considered

- **Keep the command, mark deprecated.** Rejected — the command cannot run successfully against the v2 schema (spec 0003 / spec 0019 have removed the columns it expects), so deprecation would be misleading. Better to remove than to ship a broken verb.
- **Move the command to the `upgrade` package.** Rejected — the `upgrade` package's contract is v1.x → v2.x. GetCandy → Lunar belongs to a v0 → v1 era that v2's upgrade path does not promise to cover. Anyone on GetCandy upgrades to v1.x first.
- **Do nothing.** Rejected — leaves a registered Artisan command in v2's public CLI surface that will fail or corrupt data if invoked.

## Migration impact

- Database migrations required: none.
- Breaking changes to the public contract surface: yes — the Artisan verb `lunar:migrate:getcandy` and the class `Lunar\Core\Console\Commands\MigrateGetCandy` are removed. Documented as a v2 breaking change.
- Upgrade path for v1.x consumers: GetCandy users must reach v1.x first (where the command still exists) and run `lunar:migrate:getcandy` there before upgrading to v2 via `lunarphp/upgrade`. Note this in the upgrade package README.
- Rector rule: the `LunarSetList::V1_TO_V2_CLASS_RENAMES` entry is removed rather than redirected. A v1.x app still referencing `Lunar\Console\Commands\MigrateGetCandy` in user code (vanishingly unlikely — it is an internal command class) will no longer be rewritten and will fail at autoload. Acceptable.
- Translation / locale impact: none — the command has no translatable strings outside its `--help` output.
- Filament / admin impact: none.

## Open questions

None.

## References

- [[0001-upgrade-package]] — supported v1.x → v2.x upgrade path
- [[0003-flatten-migrations]] — v2 flat baseline (removed the columns this command rewrites)
- [[0019-attribute-system-redesign]] — removed the polymorphic `attributes.type` column the command targets
