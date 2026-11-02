<?php

use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * v1 → v2 upgrade data step: add `orders.internal_notes`.
 *
 * v2 keeps the staff-only order note in its own column, so the panel never
 * writes over `orders.notes`, which holds the customer's own notes from
 * checkout. v1 had a single `notes` column; its values are left where they
 * are, since nothing records whether a given note came from the customer or
 * from staff.
 *
 * Guarded so re-runs and already-v2 databases are no-ops. There is no `down()`:
 * upgrade-package data migrations are one-way — recover from a backup if an
 * upgrade fails rather than attempting to reverse a data move.
 */
return new class extends Migration
{
    public function up(): void
    {
        $table = $this->prefix.'orders';

        if (! Schema::hasTable($table) || Schema::hasColumn($table, 'internal_notes')) {
            return;
        }

        Schema::table($table, function ($blueprint) {
            $blueprint->text('internal_notes')->nullable()->after('notes');
        });
    }
};
