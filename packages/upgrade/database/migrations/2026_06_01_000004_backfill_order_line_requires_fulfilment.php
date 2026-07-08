<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * v1 → v2 upgrade data step (spec 0031): backfill
 * `order_lines.requires_shipping` and `requires_fulfilment`.
 *
 * v2 keys the fulfilment rollup on a stored `requires_fulfilment` snapshot —
 * a superset of `requires_shipping` (physical goods plus anything that needs
 * provisioning). v1 had neither column, so both are added here (the v2
 * baseline is marked-run by the ledger rewrite, so the schema delta is
 * applied here, mirroring the baseline), then physical lines
 * (`type = 'physical'`) are marked as shippable and needing fulfilment;
 * everything else keeps the columns' `false` default. Historical v1
 * fulfilments don't exist, so no `Fulfilment` rows are created and `method`
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

        if (! Schema::hasTable($table)) {
            return;
        }

        $this->ensureColumns($table);

        DB::table($table)
            ->where('type', 'physical')
            ->where('requires_fulfilment', false)
            ->update([
                'requires_shipping' => true,
                'requires_fulfilment' => true,
            ]);
    }

    /**
     * Add the requires_shipping / requires_fulfilment columns if the baseline
     * migrations have not (mirrors ..._create_order_lines_table); v1 had
     * neither.
     */
    protected function ensureColumns(string $table): void
    {
        foreach (['requires_shipping', 'requires_fulfilment'] as $column) {
            if (Schema::hasColumn($table, $column)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($column) {
                $blueprint->boolean($column)->default(false)->index();
            });
        }
    }
};
