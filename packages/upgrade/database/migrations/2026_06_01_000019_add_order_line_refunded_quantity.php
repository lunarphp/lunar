<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * v1 → v2 upgrade data step: add `order_lines.refunded_quantity`.
 *
 * v2 records line-item refunds in `refund_lines` and keeps a rollup of the
 * refunded quantity on each order line, which `RefundOrder` increments. The
 * ledger rewrite marks the `order_lines` baseline as run, so v1 databases
 * never receive the column from it. Existing lines have had nothing refunded
 * through `refund_lines`, so the default of 0 is the correct backfill.
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

        if (! Schema::hasTable($table) || Schema::hasColumn($table, 'refunded_quantity')) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->unsignedInteger('refunded_quantity')
                ->default(0)
                ->comment('Rollup of refund_lines.quantity for this line');
        });
    }
};
