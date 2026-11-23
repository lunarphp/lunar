<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * v1 → v2 upgrade data step: add `product_options.type`.
 *
 * v2 product options are text, colour or swatch options. The ledger rewrite
 * marks the `product_options` baseline as run, so v1 databases never receive
 * the column from it. Every v1 option is a text option, so the column's
 * `text` default is the correct backfill.
 *
 * Guarded so re-runs and already-v2 databases are no-ops. One-way, no down().
 */
return new class extends Migration
{
    public function up(): void
    {
        $table = $this->prefix.'product_options';

        if (! Schema::hasTable($table) || Schema::hasColumn($table, 'type')) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->string('type')->default('text')->index();
        });
    }
};
