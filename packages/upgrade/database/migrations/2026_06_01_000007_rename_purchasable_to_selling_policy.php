<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * v1 → v2 upgrade data step: rename `product_variants.purchasable` to
 * `selling_policy` and reconcile its stored values (spec 0048).
 *
 * The column holds a selling-policy mode (`always` / `in_stock` /
 * `in_stock_or_on_backorder`), now backed by the `SellingPolicy` enum. v1 named
 * it `purchasable`, colliding with the line `purchasable` morph and the
 * `customer_group_product.purchasable` boolean; v2 renames it. The [[0038]]
 * stock backfill deliberately left this column alone, so this step is sequenced
 * after it.
 *
 * Value reconciliation is total so the backed-enum cast can never throw:
 * `in_stock_or_backorder` (a `_on`-less typo) maps to `in_stock_or_on_backorder`,
 * the three recognised values pass through, and any other rogue value falls back
 * to the safe `always` default.
 *
 * Guarded so re-runs and already-v2 databases are no-ops. There is no `down()`:
 * upgrade-package data migrations are one-way — recover from a backup if an
 * upgrade fails rather than attempting to reverse a data move.
 */
return new class extends Migration
{
    public function up(): void
    {
        $table = $this->prefix.'product_variants';

        if (! Schema::hasTable($table)) {
            return;
        }

        if (Schema::hasColumn($table, 'purchasable') && ! Schema::hasColumn($table, 'selling_policy')) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->renameColumn('purchasable', 'selling_policy');
            });
        }

        if (! Schema::hasColumn($table, 'selling_policy')) {
            return;
        }

        $recognised = ['always', 'in_stock', 'in_stock_or_on_backorder'];

        DB::table($table)
            ->where('selling_policy', 'in_stock_or_backorder')
            ->update(['selling_policy' => 'in_stock_or_on_backorder']);

        DB::table($table)
            ->whereNotIn('selling_policy', $recognised)
            ->update(['selling_policy' => 'always']);
    }
};
