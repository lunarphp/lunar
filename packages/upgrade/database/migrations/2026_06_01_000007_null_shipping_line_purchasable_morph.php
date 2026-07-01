<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * v1 → v2 upgrade data step: null the shipping-line purchasable morph.
 *
 * v1 persisted shipping lines with a fake morph (`purchasable_type` =
 * `ShippingOption::class`, `purchasable_id` = 1) to satisfy a then-mandatory
 * column — there is no `ShippingOption` table, so the morph never resolved.
 * v2 makes the morph nullable and treats shipping lines as self-describing,
 * reading the snapshot columns (`description`, `unit_price`, `meta`, …). Null
 * the morph on every `type = 'shipping'` row so it matches what v2 now writes;
 * the snapshot columns are left untouched.
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

        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'purchasable_type')) {
            return;
        }

        DB::table($table)
            ->where('type', 'shipping')
            ->whereNotNull('purchasable_type')
            ->update([
                'purchasable_type' => null,
                'purchasable_id' => null,
            ]);
    }
};
