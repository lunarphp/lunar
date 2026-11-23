<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * v1 → v2 upgrade data step: record which lines core added for a discount
 * reward, and drop the unused `cart_line_discount` pivot (spec core-values-out-of-meta).
 *
 * v2 sets `added_by_discount_id` on the cart line a BuyXGetY discount adds
 * and copies it to the order line. v1 never marked the lines it added, so
 * every existing line stays null and there is nothing to backfill.
 *
 * Neither v1 nor v2 core uses `cart_line_discount`. It is dropped only when
 * empty, so rows a store wrote there itself are kept.
 *
 * Guarded so re-runs and already-v2 databases are no-ops. One-way, no down().
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['cart_lines', 'order_lines'] as $table) {
            $this->addAddedByDiscount($this->prefix.$table);
        }

        $pivot = $this->prefix.'cart_line_discount';

        if (Schema::hasTable($pivot) && ! DB::table($pivot)->exists()) {
            Schema::drop($pivot);
        }
    }

    protected function addAddedByDiscount(string $table): void
    {
        if (! Schema::hasTable($table) || Schema::hasColumn($table, 'added_by_discount_id')) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->foreignId('added_by_discount_id')
                ->nullable()
                ->constrained($this->prefix.'discounts')
                ->nullOnDelete();
        });
    }
};
