<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * v1 -> v2 upgrade data step: add the persisted cart totals columns (spec 0076).
 *
 * v2 writes each calculation's totals to `carts` / `cart_lines` and serves the
 * next calculate() from those rows while they are fresh. The create-table
 * baselines carry the columns for fresh installs; the baseline is marked run
 * by the ledger rewrite, so the schema delta is applied here for upgraded
 * databases. No backfill: `revision` defaults to 0 and `calculated_revision`
 * is NULL, so every upgraded cart reads as "not calculated" and the next
 * calculate() fills it.
 *
 * Guarded per column so re-runs and already-v2 databases are no-ops. There is
 * no `down()`: upgrade-package data migrations are one-way — recover from a
 * backup if an upgrade fails rather than attempting to reverse a data move.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->addCartColumns();
        $this->addCartLineColumns();
    }

    protected function addCartColumns(): void
    {
        $table = $this->prefix.'carts';

        if (! Schema::hasTable($table)) {
            return;
        }

        $missing = fn (string $column) => ! Schema::hasColumn($table, $column);

        Schema::table($table, function (Blueprint $blueprint) use ($missing) {
            foreach ([
                'sub_total',
                'sub_total_discounted',
                'discount_total',
                'shipping_sub_total',
                'shipping_tax_total',
                'shipping_total',
                'tax_total',
                'total',
            ] as $column) {
                if ($missing($column)) {
                    $blueprint->unsignedBigInteger($column)->nullable();
                }
            }

            foreach (['tax_breakdown', 'shipping_breakdown', 'discount_breakdown', 'free_items'] as $column) {
                if ($missing($column)) {
                    $blueprint->jsonb($column)->nullable();
                }
            }

            if ($missing('revision')) {
                $blueprint->unsignedInteger('revision')->default(0);
            }

            if ($missing('calculated_revision')) {
                $blueprint->unsignedInteger('calculated_revision')->nullable();
            }

            if ($missing('calculated_at')) {
                $blueprint->timestamp('calculated_at')->nullable();
            }
        });
    }

    protected function addCartLineColumns(): void
    {
        $table = $this->prefix.'cart_lines';

        if (! Schema::hasTable($table)) {
            return;
        }

        $missing = fn (string $column) => ! Schema::hasColumn($table, $column);

        Schema::table($table, function (Blueprint $blueprint) use ($missing) {
            foreach ([
                'unit_price',
                'unit_price_incl_tax',
                'sub_total',
                'sub_total_discounted',
                'discount_total',
                'tax_total',
                'total',
            ] as $column) {
                if ($missing($column)) {
                    $blueprint->unsignedBigInteger($column)->nullable();
                }
            }

            if ($missing('tax_breakdown')) {
                $blueprint->jsonb('tax_breakdown')->nullable();
            }

            if ($missing('promotion_description')) {
                $blueprint->string('promotion_description')->nullable();
            }
        });
    }
};
