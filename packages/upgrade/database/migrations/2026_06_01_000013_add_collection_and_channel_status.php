<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * v1 → v2 upgrade data step: add the `status` column to `collections` and `channels`.
 *
 * v2 gives both models a lifecycle `status` (collections: draft|published|archived;
 * channels: active|inactive), but v1 had neither column — and no earlier upgrade step
 * adds them (the stage-3 catalogue-status work only ever landed for orders, as
 * 000009). Because the ledger rewrite baselines the v2 create-table migrations as
 * already-run, an upgraded database reaches v2 with `collections`/`channels` carrying
 * `deleted_at` but no `status`. Add the column matching the v2 baseline definition,
 * then backfill every existing row to a visible state:
 *
 *   - collections -> 'published'. v1 collections were not lifecycle-gated, so live
 *     ones must NOT inherit the 'draft' column default — that would hide a merchant's
 *     entire collection tree after the upgrade. The following reconcile step (000014)
 *     re-marks the soft-deleted ones 'archived'.
 *   - channels -> 'active'. v1 channel enable/disable lived on the `channelables`
 *     pivot, not on `channels`, so there is nothing to map from; 000014 re-marks the
 *     soft-deleted ones 'inactive'.
 *
 * Guarded on the column being absent, so fresh v2 installs (which already have
 * `status`) and re-runs are no-ops. One-way, no down().
 */
return new class extends Migration
{
    public function up(): void
    {
        $collections = $this->prefix.'collections';

        if (Schema::hasTable($collections) && ! Schema::hasColumn($collections, 'status')) {
            Schema::table($collections, function ($blueprint) {
                $blueprint->string('status')->default('draft')->index();
            });

            DB::table($collections)->update(['status' => 'published']);
        }

        $channels = $this->prefix.'channels';

        if (Schema::hasTable($channels) && ! Schema::hasColumn($channels, 'status')) {
            Schema::table($channels, function ($blueprint) {
                $blueprint->string('status')->default('active')->index();
            });

            DB::table($channels)->update(['status' => 'active']);
        }
    }
};
