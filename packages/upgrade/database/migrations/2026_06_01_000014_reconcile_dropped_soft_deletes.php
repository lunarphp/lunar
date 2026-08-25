<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * v1 → v2 upgrade data step: reconcile the models that dropped SoftDeletes.
 *
 * v2 removed the SoftDeletes trait from Product, ProductVariant, Channel and
 * Collection (Cart and Staff keep it), replacing soft-deletion with explicit state:
 * products/collections retire via `status` ('archived'), channels via `status`
 * ('inactive'), variants via the `enabled` flag. The v2 create-table migrations no
 * longer add `deleted_at`, so a fresh install has no such column on these tables.
 *
 * An upgraded v1 database still carries the v1 `deleted_at` column and every
 * soft-deleted row, and nothing in v2 reads it, so those records resurface as live /
 * enabled catalogue entries. Map each v1 soft-delete onto the v2 state, then drop the
 * orphaned column so the upgraded schema matches a fresh install.
 *
 * The hidden-state column is guaranteed present by the time this runs — products keep
 * their v1 `status`, `product_variants.enabled` is added by 000012, and
 * `collections`/`channels.status` by 000013 — so this deliberately carries NO
 * per-column guard. A missing column here must fail loud rather than silently skip the
 * mapping and drop `deleted_at` anyway, which would resurface soft-deleted rows as live
 * with no record. The `deleted_at` presence guard remains, so re-runs, fresh installs,
 * and the SoftDeletes-keeping Cart/Staff tables are untouched.
 *
 * Rows are kept, not deleted: historical order lines reference retired variants and
 * must still resolve their purchasable. One-way, no down().
 */
return new class extends Migration
{
    public function up(): void
    {
        // [table (unprefixed), v2 "hidden" column, value written for soft-deleted rows]
        $reconcile = [
            ['products', 'status', 'archived'],
            ['product_variants', 'enabled', false],
            ['collections', 'status', 'archived'],
            ['channels', 'status', 'inactive'],
        ];

        foreach ($reconcile as [$name, $column, $hidden]) {
            $table = $this->prefix.$name;

            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'deleted_at')) {
                continue;
            }

            DB::table($table)
                ->whereNotNull('deleted_at')
                ->update([$column => $hidden]);

            Schema::table($table, function ($blueprint) {
                $blueprint->dropColumn('deleted_at');
            });
        }
    }
};
