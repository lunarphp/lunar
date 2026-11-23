<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * v1 → v2 upgrade data step: drop `tax_zones.price_display`.
 *
 * v2 decides tax-inclusive display per region (`regions.prices_inc_tax`), so
 * the v1 tax zone column is gone from the baseline. Guarded so re-runs and
 * already-v2 databases are no-ops. One-way, no down().
 */
return new class extends Migration
{
    public function up(): void
    {
        $table = $this->prefix.'tax_zones';

        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'price_display')) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->dropColumn('price_display');
        });
    }
};
