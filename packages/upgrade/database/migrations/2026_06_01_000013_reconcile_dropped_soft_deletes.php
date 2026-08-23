<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * v1 → v2 upgrade data step: reconcile the models that dropped SoftDeletes.
 *
 * v2 removed the SoftDeletes trait from Product, ProductVariant, Channel and
 * Collection (Cart and Staff keep it), replacing soft-deletion with explicit
 * state: products and collections retire via `status` ('archived'), channels via
 * `status` ('inactive'), variants via the `enabled` flag. The v2 create-table
 * migrations no longer add `deleted_at`, so a fresh install has no such column on
 * these tables.
 *
 * An upgraded v1 database still carries the v1 `deleted_at` column and every
 * soft-deleted row. Nothing in v2 reads that column, so those records resurface as
 * live / enabled catalogue entries after the upgrade (a v1 soft-deleted variant is
 * `enabled = true` from 000012's backfill; a soft-deleted product keeps its old
 * `published` status). Map each v1 soft-delete onto the v2 state, then drop the
 * orphaned column so the upgraded schema matches a fresh install.
 *
 * Rows are kept, not deleted: historical order lines reference retired variants and
 * must still resolve their purchasable. Guarded per table on the presence of
 * `deleted_at`, so re-runs, already-v2 databases, and the SoftDeletes-keeping
 * Cart/Staff tables are untouched. There is no `down()`: upgrade-package data
 * migrations are one-way — recover from a backup rather than reversing a data move.
 */
return new class extends Migration {
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
            $table = $this->prefix . $name;

            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'deleted_at')) {
                continue;
            }

            if (Schema::hasColumn($table, $column)) {
                DB::table($table)
                    ->whereNotNull('deleted_at')
                    ->update([$column => $hidden]);
            }

            Schema::table($table, function ($blueprint) {
                $blueprint->dropColumn('deleted_at');
            });
        }
    }
};
