<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * v1 → v2 upgrade data step: add `staff.preferred_locale`.
 *
 * The v2 panel stores each staff member's interface locale; v1 staff have
 * none and fall back to the application locale. Guarded so re-runs,
 * already-v2 databases and headless v1 stores without staff are no-ops.
 * One-way, no down().
 */
return new class extends Migration
{
    public function up(): void
    {
        $table = $this->prefix.'staff';

        if (! Schema::hasTable($table) || Schema::hasColumn($table, 'preferred_locale')) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->string('preferred_locale')->nullable()->after('email');
        });
    }
};
