<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * v1 → v2 upgrade data step: say which locations accept pickup.
 *
 * v2 only offers pickup at a location with `accepts_pickup` set. v1 fulfilled
 * every collection order from the default location, so that location accepts
 * pickup and any others don't.
 *
 * Runs after backfill_stock_from_variants, which creates `locations` on a v1
 * database. The default location is only marked when this step adds the
 * column, so a re-run never overrides a choice staff made since. One-way, no
 * down().
 */
return new class extends Migration
{
    public function up(): void
    {
        $locations = $this->prefix.'locations';

        if (! Schema::hasTable($locations) || Schema::hasColumn($locations, 'accepts_pickup')) {
            return;
        }

        Schema::table($locations, function (Blueprint $table) {
            $table->boolean('accepts_pickup')->default(false)->index();
        });

        DB::table($locations)->where('default', true)->update(['accepts_pickup' => true]);
    }
};
