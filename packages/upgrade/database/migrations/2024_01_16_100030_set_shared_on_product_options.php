<?php

use Illuminate\Support\Facades\DB;
use Lunar\Core\Base\Migration;

/**
 * v1 → v2 upgrade data step: mark all existing product_options as shared.
 *
 * The schema column is added in the v2 baseline create migration with
 * default(false); this migration backfills `shared = true` for rows that
 * existed before v2 (matching the original v1.x behaviour).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table($this->prefix.'product_options')->update([
            'shared' => true,
        ]);
    }

    public function down(): void
    {
        // No-op: the column is dropped by the table reversal in core.
    }
};
