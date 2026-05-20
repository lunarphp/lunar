<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Base\Migration;

/**
 * v1 → v2 upgrade data step: rename legacy `catalogue:*` admin permissions
 * to the v2 `catalog:*` / `sales:*` naming.
 *
 * Fresh v2 installs never had the old names, so this only does work on
 * upgrades. Idempotent — re-running is safe.
 */
return new class extends Migration
{
    public function up(): void
    {
        $table = config('permission.table_names.permissions');

        if (! $table || ! Schema::hasTable($table)) {
            return;
        }

        $renames = [
            'catalogue:manage-products' => 'catalog:manage-products',
            'catalogue:manage-collections' => 'catalog:manage-collections',
            'catalogue:manage-orders' => 'sales:manage-orders',
            'catalogue:manage-customers' => 'sales:manage-customers',
            'catalogue:manage-discounts' => 'sales:manage-discounts',
        ];

        foreach ($renames as $from => $to) {
            DB::table($table)
                ->where('name', $from)
                ->update(['name' => $to]);
        }
    }

    public function down(): void
    {
        // Irreversible: the v2 names overlap with permissions a fresh install
        // would create on its own, so we cannot safely restore the old names.
    }
};
