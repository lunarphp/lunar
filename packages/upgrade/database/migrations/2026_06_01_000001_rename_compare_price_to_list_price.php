<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * v1 → v2 upgrade data step: rename the catalogue price `compare_price`
 * column to `list_price` (spec 0017). Guarded so re-runs and already-v2
 * databases are no-ops.
 */
return new class extends Migration
{
    public function up(): void
    {
        $table = $this->prefix.'prices';

        if (! Schema::hasColumn($table, 'compare_price') || Schema::hasColumn($table, 'list_price')) {
            return;
        }

        Schema::table($table, function (Blueprint $table) {
            $table->renameColumn('compare_price', 'list_price');
        });
    }

    public function down(): void
    {
        $table = $this->prefix.'prices';

        if (! Schema::hasColumn($table, 'list_price') || Schema::hasColumn($table, 'compare_price')) {
            return;
        }

        Schema::table($table, function (Blueprint $table) {
            $table->renameColumn('list_price', 'compare_price');
        });
    }
};
