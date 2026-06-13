<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * v1 → v2 upgrade data step (spec 0031): backfill
 * `order_lines.requires_fulfilment`.
 *
 * v2 keys the fulfilment rollup on a stored `requires_fulfilment` snapshot —
 * a superset of `requires_shipping` (physical goods plus anything that needs
 * provisioning). v1 had no such signal, so — mirroring the `requires_shipping`
 * backfill — physical lines (`type = 'physical'`) are marked as needing
 * fulfilment and everything else keeps the column's `false` default. Historical
 * v1 fulfilments don't exist, so no `Fulfilment` rows are created and `method`
 * keeps its `shipping` default (per the 0022 upgrade choice).
 *
 * Guarded so re-runs and already-v2 databases are no-ops. There is no `down()`:
 * upgrade-package data migrations are one-way — recover from a backup if an
 * upgrade fails rather than attempting to reverse a data move.
 */
return new class extends Migration
{
    public function up(): void
    {
        $table = $this->prefix.'order_lines';

        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'requires_fulfilment')) {
            return;
        }

        DB::table($table)
            ->where('type', 'physical')
            ->where('requires_fulfilment', false)
            ->update(['requires_fulfilment' => true]);
    }
};
