<?php

use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * v1 → v2 upgrade data step: add `carts.remember_token`.
 *
 * v2 can restore a guest cart from a long-lived cookie after the session
 * expires (spec 0090). The column holds the hash of that cookie's token. v1
 * carts have no token; each gets one the next time it becomes a session's
 * cart, so no backfill is needed.
 *
 * Guarded so re-runs and already-v2 databases are no-ops. There is no `down()`:
 * upgrade-package data migrations are one-way — recover from a backup if an
 * upgrade fails rather than attempting to reverse a data move.
 */
return new class extends Migration
{
    public function up(): void
    {
        $table = $this->prefix.'carts';

        if (! Schema::hasTable($table) || Schema::hasColumn($table, 'remember_token')) {
            return;
        }

        Schema::table($table, function ($blueprint) {
            $blueprint->string('remember_token', 64)->nullable()->unique();
        });
    }
};
