<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * v1 -> v2 upgrade data step: seed a single default `Region` from the v1 default
 * channel/currency/language/tax-zone and point existing carts and orders at it.
 *
 * v1 had no region concept; v2 routes channel/currency/language/tax behaviour
 * through a `Region`, stamping `region_id` on carts and orders. For a v1 store
 * this creates the catch-all default region (no countries assigned) and backfills
 * `region_id` on every cart/order that lacks one.
 *
 * Self-sufficient: the v2 baseline that adds the region tables and the
 * `region_id` columns is marked-run by the ledger rewrite, so the schema delta is
 * applied here (guarded, mirroring the baseline). A pre-existing default region
 * means already-v2 / re-run, so it is a no-op. There is no `down()` — upgrade data
 * migrations are one-way; recover from a backup.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->ensureRegionTables();
        $this->ensureRegionForeignKeys();

        $regions = $this->prefix.'regions';

        // A default region already present means already-v2 / re-run.
        if (DB::table($regions)->where('default', true)->exists()) {
            return;
        }

        $channelId = $this->defaultId('channels');
        $currencyId = $this->defaultId('currencies');
        $languageId = $this->defaultId('languages');

        // The region's channel/currency/language are non-nullable; without them
        // there is nothing to seed from, so leave it for a later re-run.
        if ($channelId === null || $currencyId === null || $languageId === null) {
            return;
        }

        $regionId = DB::table($regions)->insertGetId([
            'name' => 'Default',
            'handle' => 'default',
            'channel_id' => $channelId,
            'currency_id' => $currencyId,
            'language_id' => $languageId,
            'tax_zone_id' => $this->defaultId('tax_zones'),
            'prices_inc_tax' => null,
            'default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach (['carts', 'orders'] as $table) {
            DB::table($this->prefix.$table)->whereNull('region_id')->update(['region_id' => $regionId]);
        }
    }

    /**
     * Create the region tables if the baseline migrations have not (mirrors
     * packages/core/database/migrations/..._create_{regions,country_region}_table).
     */
    protected function ensureRegionTables(): void
    {
        if (! Schema::hasTable($this->prefix.'regions')) {
            Schema::create($this->prefix.'regions', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('handle')->unique();
                $table->foreignId('channel_id')->constrained($this->prefix.'channels');
                $table->foreignId('currency_id')->constrained($this->prefix.'currencies');
                $table->foreignId('language_id')->constrained($this->prefix.'languages');
                $table->foreignId('tax_zone_id')->nullable()->constrained($this->prefix.'tax_zones')->nullOnDelete();
                $table->boolean('prices_inc_tax')->nullable();
                $table->boolean('default')->default(false)->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable($this->prefix.'country_region')) {
            Schema::create($this->prefix.'country_region', function (Blueprint $table) {
                $table->id();
                $table->foreignId('country_id')->constrained($this->prefix.'countries')->cascadeOnDelete();
                $table->foreignId('region_id')->constrained($this->prefix.'regions')->cascadeOnDelete();
                $table->unique(['country_id', 'region_id']);
            });
        }
    }

    /**
     * Add the nullable `region_id` FK to carts and orders if the baseline has not
     * (mirrors ..._add_region_id_to_carts_and_orders).
     */
    protected function ensureRegionForeignKeys(): void
    {
        foreach (['carts', 'orders'] as $table) {
            $name = $this->prefix.$table;

            if (Schema::hasTable($name) && ! Schema::hasColumn($name, 'region_id')) {
                Schema::table($name, function (Blueprint $table) {
                    $table->foreignId('region_id')->nullable()->constrained($this->prefix.'regions')->nullOnDelete();
                });
            }
        }
    }

    /**
     * The default row's id for a foundation table, falling back to the lowest id.
     */
    protected function defaultId(string $table): ?int
    {
        $name = $this->prefix.$table;

        if (! Schema::hasTable($name)) {
            return null;
        }

        $id = DB::table($name)->where('default', true)->value('id')
            ?? DB::table($name)->orderBy('id')->value('id');

        return $id === null ? null : (int) $id;
    }
};
