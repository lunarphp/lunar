<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Lunar\Core\Database\Migration;

/**
 * v1 → v2 upgrade data step (spec 0056): add `product_types.handle`,
 * `status`, `description`, `default_tax_class_id` and `attribute_data`.
 *
 * v2 gives product types a unique kebab-case handle (the stable programmatic
 * key), an active/draft status gating the product create flow, an internal
 * description, a default tax class for new products, and attribute data for
 * the type's own fields. v1 had none of these columns: handles are backfilled
 * from the type name, suffixed until unique (`type`, `type-2`, ...), and
 * every existing type is marked `active` so product creation keeps every
 * type available after the upgrade.
 *
 * Guarded so re-runs and already-v2 databases are no-ops. There is no `down()`:
 * upgrade-package data migrations are one-way — recover from a backup if an
 * upgrade fails rather than attempting to reverse a data move.
 */
return new class extends Migration
{
    public function up(): void
    {
        $table = $this->prefix.'product_types';

        if (! Schema::hasTable($table) || Schema::hasColumn($table, 'handle')) {
            return;
        }

        // Added nullable, backfilled, then constrained — the unique index
        // cannot be created while every row holds NULL on some drivers.
        Schema::table($table, function ($blueprint) {
            $blueprint->string('handle')->nullable();
            $blueprint->string('status')->default('active')->index();
            $blueprint->text('description')->nullable();
            $blueprint->foreignId('default_tax_class_id')->nullable();
            $blueprint->jsonb('attribute_data')->nullable();
        });

        $taken = [];

        DB::table($table)->orderBy('id')->each(function ($productType) use ($table, &$taken) {
            $base = Str::slug($productType->name) ?: 'product-type';
            $handle = $base;

            for ($suffix = 2; isset($taken[$handle]); $suffix++) {
                $handle = $base.'-'.$suffix;
            }

            $taken[$handle] = true;

            DB::table($table)->where('id', $productType->id)->update([
                'handle' => $handle,
                'status' => 'active',
            ]);
        });

        Schema::table($table, function ($blueprint) {
            $blueprint->string('handle')->nullable(false)->change();
            $blueprint->unique('handle');
            $blueprint->foreign('default_tax_class_id')
                ->references('id')
                ->on($this->prefix.'tax_classes')
                ->nullOnDelete();
        });
    }
};
