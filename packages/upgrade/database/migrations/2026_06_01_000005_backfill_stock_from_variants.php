<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * v1 -> v2 upgrade data step (spec 0038): move the flat `product_variants.stock`
 * column into the per-location inventory model.
 *
 * v1 stored a single `stock` integer per variant; v2 tracks stock as `StockLevel`
 * rows behind an append-only `StockMovement` ledger, with a cached rollup on the
 * variant. For each variant with non-zero stock this creates a `StockLevel` at the
 * default location (`on_hand = stock`) and an `OpeningBalance` movement, seeds the
 * rollup, then drops the old column. `backorder` and `purchasable` stay as-is —
 * they are selling policy, not physical quantity.
 *
 * Self-sufficient: the v2 baseline that adds the rollup columns / new tables is
 * marked-run by the ledger rewrite, so the schema delta is applied here (guarded,
 * mirroring the baseline) — including the `locations` table the stock tables
 * reference, since v1 has none. Re-runs and already-v2 databases are no-ops.
 * There is no `down()` — upgrade data migrations are one-way; recover from a backup.
 */
return new class extends Migration
{
    public function up(): void
    {
        $variants = $this->prefix.'product_variants';

        // The `stock` column is the v1 signal; absent means already v2 / nothing to do.
        if (! Schema::hasTable($variants) || ! Schema::hasColumn($variants, 'stock')) {
            return;
        }

        $this->addRollupColumns($variants);
        $this->ensureLocationsTable();
        $this->ensureStockTables();

        $this->backfill($variants, $this->defaultLocationId());

        Schema::table($variants, function (Blueprint $table) {
            $table->dropColumn('stock');
        });
    }

    protected function addRollupColumns(string $variants): void
    {
        $columns = ['stock_on_hand', 'stock_incoming', 'stock_committed', 'stock_reserved', 'stock_unavailable'];

        foreach ($columns as $column) {
            if (Schema::hasColumn($variants, $column)) {
                continue;
            }

            Schema::table($variants, function (Blueprint $table) use ($column) {
                $table->integer($column)->default(0);
            });
        }

        if (! Schema::hasColumn($variants, 'stock_available')) {
            Schema::table($variants, function (Blueprint $table) {
                $table->integer('stock_available')->default(0)->index();
            });
        }
    }

    /**
     * Create the locations table if the baseline migrations have not (mirrors
     * packages/core/database/migrations/..._create_locations_table). The stock
     * tables reference it, and v1 has no locations at all. `public_id` arrives
     * with the later add_public_id_to_addressable_models step.
     */
    protected function ensureLocationsTable(): void
    {
        if (! Schema::hasTable($this->prefix.'locations')) {
            Schema::create($this->prefix.'locations', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('handle')->unique();
                $table->boolean('default')->default(false)->index();
                $table->jsonb('meta')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Create the stock tables if the baseline migrations have not (mirrors
     * packages/core/database/migrations/..._create_stock_{levels,movements}_table).
     */
    protected function ensureStockTables(): void
    {
        if (! Schema::hasTable($this->prefix.'stock_levels')) {
            Schema::create($this->prefix.'stock_levels', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_variant_id')->constrained($this->prefix.'product_variants')->cascadeOnDelete();
                $table->foreignId('location_id')->constrained($this->prefix.'locations')->restrictOnDelete();
                $table->integer('on_hand')->default(0);
                $table->integer('incoming')->default(0);
                $table->integer('committed')->default(0);
                $table->integer('unavailable')->default(0);
                $table->jsonb('meta')->nullable();
                $table->timestamps();
                $table->unique(['product_variant_id', 'location_id']);
            });
        }

        if (! Schema::hasTable($this->prefix.'stock_movements')) {
            Schema::create($this->prefix.'stock_movements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_variant_id')->constrained($this->prefix.'product_variants')->cascadeOnDelete();
                $table->foreignId('location_id')->constrained($this->prefix.'locations')->restrictOnDelete();
                $table->integer('quantity');
                $table->string('type')->index();
                $table->nullableMorphs('source');
                $table->string('note')->nullable();
                $table->nullableMorphs('causer');
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    protected function defaultLocationId(): int
    {
        $locations = $this->prefix.'locations';

        return DB::table($locations)->where('default', true)->value('id')
            ?? DB::table($locations)->orderBy('id')->value('id')
            ?? DB::table($locations)->insertGetId([
                'name' => 'Default',
                'handle' => 'default',
                'default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
    }

    protected function backfill(string $variants, int $locationId): void
    {
        DB::table($variants)->where('stock', '!=', 0)->orderBy('id')->each(function ($variant) use ($variants, $locationId) {
            $stock = (int) $variant->stock;

            DB::table($this->prefix.'stock_levels')->insert([
                'product_variant_id' => $variant->id,
                'location_id' => $locationId,
                'on_hand' => $stock,
                'incoming' => 0,
                'committed' => 0,
                'unavailable' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table($this->prefix.'stock_movements')->insert([
                'product_variant_id' => $variant->id,
                'location_id' => $locationId,
                'quantity' => $stock,
                'type' => 'opening_balance',
                'created_at' => now(),
            ]);

            // available = on_hand - committed - reserved - unavailable = stock.
            DB::table($variants)->where('id', $variant->id)->update([
                'stock_on_hand' => $stock,
                'stock_available' => $stock,
            ]);
        });
    }
};
