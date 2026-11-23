<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;
use Lunar\Core\Facades\DB;

/**
 * v1 → v2 upgrade data step: bring the `products` and `collections` columns
 * into the shape of their v2 baselines.
 *
 * - `products.status` defaults to `draft`, as the baseline does.
 * - `products.name` and `collections.name` become NOT NULL.
 *   2026_06_01_000002 adds them as nullable so it can backfill them from
 *   `attribute_data`. A row that had no v1 `name` attribute gets an empty name
 *   in the default language, which the panel shows for editing.
 *
 * Guarded so re-runs and already-v2 databases are no-ops. One-way, no down().
 */
return new class extends Migration
{
    public function up(): void
    {
        $products = $this->prefix.'products';

        if (Schema::hasTable($products) && Schema::hasColumn($products, 'status')) {
            Schema::table($products, function (Blueprint $blueprint) {
                $blueprint->string('status')->default('draft')->change();
            });
        }

        foreach (['products', 'collections'] as $name) {
            $table = $this->prefix.$name;

            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'name')) {
                continue;
            }

            DB::table($table)->whereNull('name')->update([
                'name' => json_encode([$this->defaultLanguage() => '']),
            ]);

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->jsonb('name')->nullable(false)->change();
            });
        }
    }

    protected function defaultLanguage(): string
    {
        $languages = $this->prefix.'languages';

        if (! Schema::hasTable($languages)) {
            return 'en';
        }

        return DB::table($languages)->where('default', true)->value('code')
            ?? DB::table($languages)->orderBy('id')->value('code')
            ?? 'en';
    }
};
