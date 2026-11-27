<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * v1 → v2 upgrade data step (spec 0082): replace the variant selling mode with
 * the `continue_selling` and `sell_against_incoming` flags.
 *
 * The mode lives in `product_variants.purchasable` on v1 and in
 * `selling_policy` on a database an earlier upgrade already renamed. It maps
 * `always` → (true, false), `in_stock` → (false, false) and
 * `in_stock_or_on_backorder` → (false, true). The `in_stock_or_backorder` typo
 * some v1 fixtures used counts as the last; any other value falls back to
 * `always`.
 *
 * v1's `backorder` allowance gives way to the tracked `incoming` bucket. So
 * variants that sold against it sell the same quantity after the upgrade, a
 * positive allowance on an `in_stock_or_on_backorder` variant is added to the
 * default location's `incoming` and to the `stock_incoming` rollup. The
 * [[0038]] stock backfill creates the stock tables first, so this step is
 * sequenced after it.
 *
 * Guarded so re-runs and already-v2 databases are no-ops. There is no `down()`:
 * upgrade-package data migrations are one-way — recover from a backup if an
 * upgrade fails rather than attempting to reverse a data move.
 */
return new class extends Migration
{
    public function up(): void
    {
        $variants = $this->prefix.'product_variants';

        if (! Schema::hasTable($variants)) {
            return;
        }

        $mode = collect(['selling_policy', 'purchasable'])
            ->first(fn (string $column): bool => Schema::hasColumn($variants, $column));

        if (! $mode) {
            return;
        }

        $this->addFlags($variants);

        DB::table($variants)->where($mode, 'in_stock_or_backorder')->update([$mode => 'in_stock_or_on_backorder']);

        DB::table($variants)->update(['continue_selling' => true, 'sell_against_incoming' => false]);
        DB::table($variants)->where($mode, 'in_stock')->update(['continue_selling' => false]);
        DB::table($variants)->where($mode, 'in_stock_or_on_backorder')->update(['continue_selling' => false, 'sell_against_incoming' => true]);

        if (Schema::hasColumn($variants, 'backorder')) {
            $this->seedIncoming($variants, $mode);
        }

        $this->dropColumns($variants, [$mode, 'backorder']);
    }

    protected function addFlags(string $variants): void
    {
        Schema::table($variants, function (Blueprint $table) use ($variants) {
            if (! Schema::hasColumn($variants, 'continue_selling')) {
                $table->boolean('continue_selling')->default(true)->index();
            }

            if (! Schema::hasColumn($variants, 'sell_against_incoming')) {
                $table->boolean('sell_against_incoming')->default(false);
            }
        });
    }

    protected function seedIncoming(string $variants, string $mode): void
    {
        $levels = $this->prefix.'stock_levels';
        $locations = $this->prefix.'locations';

        if (! Schema::hasTable($levels) || ! Schema::hasTable($locations)) {
            return;
        }

        $backordered = DB::table($variants)
            ->where($mode, 'in_stock_or_on_backorder')
            ->where('backorder', '>', 0);

        if (! $backordered->exists()) {
            return;
        }

        $locationId = DB::table($locations)->where('default', true)->value('id')
            ?? DB::table($locations)->orderBy('id')->value('id')
            ?? DB::table($locations)->insertGetId([
                'name' => 'Default',
                'handle' => 'default',
                'default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        $backordered->orderBy('id')->each(function ($variant) use ($variants, $levels, $locationId) {
            $level = DB::table($levels)
                ->where('product_variant_id', $variant->id)
                ->where('location_id', $locationId);

            if ($level->exists()) {
                $level->increment('incoming', (int) $variant->backorder);
            } else {
                DB::table($levels)->insert([
                    'product_variant_id' => $variant->id,
                    'location_id' => $locationId,
                    'on_hand' => 0,
                    'incoming' => (int) $variant->backorder,
                    'committed' => 0,
                    'unavailable' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if (Schema::hasColumn($variants, 'stock_incoming')) {
                DB::table($variants)->where('id', $variant->id)->update([
                    'stock_incoming' => (int) DB::table($levels)->where('product_variant_id', $variant->id)->sum('incoming'),
                ]);
            }
        });
    }

    /**
     * @param  list<string>  $columns
     */
    protected function dropColumns(string $variants, array $columns): void
    {
        $columns = array_values(array_filter($columns, fn (string $column): bool => Schema::hasColumn($variants, $column)));

        foreach ($columns as $column) {
            if (Schema::hasIndex($variants, [$column])) {
                Schema::table($variants, fn (Blueprint $table) => $table->dropIndex([$column]));
            }
        }

        if ($columns !== []) {
            Schema::table($variants, fn (Blueprint $table) => $table->dropColumn($columns));
        }
    }
};
