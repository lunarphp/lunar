<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * v1 → v2 upgrade data step: add `addresses.last_used_at`.
 *
 * v2 casts `Address::$last_used_at` so a checkout can record when a saved
 * address was last used. The ledger rewrite marks the `addresses` baseline as
 * run, so v1 databases never receive the column from it. v1 recorded no such
 * time, so existing addresses start with none.
 *
 * Guarded so re-runs and already-v2 databases are no-ops. One-way, no down().
 */
return new class extends Migration
{
    public function up(): void
    {
        $table = $this->prefix.'addresses';

        if (! Schema::hasTable($table) || Schema::hasColumn($table, 'last_used_at')) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->timestamp('last_used_at')->nullable();
        });
    }
};
