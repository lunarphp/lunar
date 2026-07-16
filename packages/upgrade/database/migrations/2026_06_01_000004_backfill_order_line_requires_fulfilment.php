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

        // Adding the columns is the v1 signal: when they already exist the
        // rows carry deliberate per-line values (already-v2 or already run)
        // and flipping them would overwrite store choices. If a crash lands
        // between the column add and the backfill, drop the two columns
        // before re-running.
        if (! $this->ensureColumns($table)) {
            return;
        }

        DB::table($table)
            ->where('type', 'physical')
            ->update([
                'requires_shipping' => true,
                'requires_fulfilment' => true,
            ]);
    }

    /**
     * Add the requires_shipping / requires_fulfilment columns if the baseline
     * migrations have not (mirrors ..._create_order_lines_table); v1 had
     * neither. Returns whether anything was added.
     */
    protected function ensureColumns(string $table): bool
    {
        $missing = array_filter(
            ['requires_shipping', 'requires_fulfilment'],
            fn (string $column): bool => ! Schema::hasColumn($table, $column),
        );

        if ($missing === []) {
            return false;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($missing) {
            foreach ($missing as $column) {
                $blueprint->boolean($column)->default(false)->index();
            }
        });

        return true;
    }
};
