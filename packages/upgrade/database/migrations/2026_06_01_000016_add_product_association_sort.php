<?php

use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * v1 → v2 upgrade data step: add `product_associations.sort`.
 *
 * v2 gives product associations an explicit, drag-reorderable position, and
 * `Product::associations()` orders by it (`->orderBy('sort')->orderBy('id')`).
 * The create-table baseline adds the column for fresh installs, but no upgrade
 * step added it for existing databases — so on an upgraded store every read of
 * the relation (opening a product in the panel, resolving cross-sells on the
 * storefront) fails with `SQLSTATE[42S22] Unknown column 'sort'`. Existing rows
 * take the column default (0), keeping their current order until a merchant
 * reorders them.
 *
 * Guarded so re-runs and already-v2 databases are no-ops. There is no `down()`:
 * upgrade-package data migrations are one-way — recover from a backup if an
 * upgrade fails rather than attempting to reverse a data move.
 */
return new class extends Migration
{
    public function up(): void
    {
        $table = $this->prefix.'product_associations';

        if (! Schema::hasTable($table) || Schema::hasColumn($table, 'sort')) {
            return;
        }

        Schema::table($table, function ($blueprint) {
            $blueprint->unsignedInteger('sort')->default(0);
        });
    }
};
