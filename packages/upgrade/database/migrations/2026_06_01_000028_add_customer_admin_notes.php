<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * v1 → v2 upgrade data step: add `customers.admin_notes`.
 *
 * The v2 panel keeps staff notes on each customer. The ledger rewrite marks
 * the `customers` baseline as run, so v1 databases never receive the column
 * from it. v1 had no such notes, so existing customers start with none.
 *
 * Guarded so re-runs and already-v2 databases are no-ops. One-way, no down().
 */
return new class extends Migration
{
    public function up(): void
    {
        $table = $this->prefix.'customers';

        if (! Schema::hasTable($table) || Schema::hasColumn($table, 'admin_notes')) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->text('admin_notes')->nullable();
        });
    }
};
