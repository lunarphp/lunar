<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * v1 → v2 upgrade data step: add `colour` to product option values (spec core-values-out-of-meta).
 *
 * v2 keeps the hex colour of a value of a colour option in a typed column. v1
 * has no colour option type, so there is nothing to backfill.
 *
 * Guarded so re-runs and already-v2 databases are no-ops. One-way, no down().
 */
return new class extends Migration
{
    public function up(): void
    {
        $table = $this->prefix.'product_option_values';

        if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'colour')) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('colour', 7)->nullable();
            });
        }
    }
};
