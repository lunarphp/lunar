<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * v1 → v2 upgrade data step: rename the table-rate-shipping `collection`
 * driver to `pickup` (spec 0077).
 *
 * v1 persisted the driver key on `shipping_methods.driver`; v2 renamed the
 * `Lunar\Shipping\Drivers\ShippingMethods\Collection` driver to `Pickup` and
 * its key to `pickup`, so existing rows must follow or the manager can no
 * longer resolve them.
 *
 * Guarded so re-runs, already-v2 databases, and stores without the
 * table-rate-shipping package are no-ops. There is no `down()`:
 * upgrade-package data migrations are one-way — recover from a backup if an
 * upgrade fails rather than attempting to reverse a data move.
 */
return new class extends Migration
{
    public function up(): void
    {
        $table = $this->prefix.'shipping_methods';

        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'driver')) {
            return;
        }

        DB::table($table)
            ->where('driver', 'collection')
            ->update(['driver' => 'pickup']);
    }
};
