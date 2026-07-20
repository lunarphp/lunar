<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Lunar\Core\Database\Migration;

/**
 * v1 → v2 upgrade data step (spec 0052): add `brands.handle` and
 * `brands.status`.
 *
 * v2 gives brands a unique kebab-case handle (the stable programmatic key)
 * and an active/draft status. v1 had neither column: handles are backfilled
 * from the brand name, suffixed until unique (`brand`, `brand-2`, ...), and
 * every existing brand is marked `active` so storefronts keep all brands
 * visible after the upgrade.
 *
 * Guarded so re-runs and already-v2 databases are no-ops. There is no `down()`:
 * upgrade-package data migrations are one-way — recover from a backup if an
 * upgrade fails rather than attempting to reverse a data move.
 */
return new class extends Migration
{
    public function up(): void
    {
        $table = $this->prefix.'brands';

        if (! Schema::hasTable($table) || Schema::hasColumn($table, 'handle')) {
            return;
        }

        // Added nullable, backfilled, then constrained — the unique index
        // cannot be created while every row holds NULL on some drivers.
        Schema::table($table, function ($blueprint) {
            $blueprint->string('handle')->nullable();
            $blueprint->string('status')->default('active')->index();
        });

        $taken = [];

        DB::table($table)->orderBy('id')->each(function ($brand) use ($table, &$taken) {
            $base = Str::slug($brand->name) ?: 'brand';
            $handle = $base;

            for ($suffix = 2; isset($taken[$handle]); $suffix++) {
                $handle = $base.'-'.$suffix;
            }

            $taken[$handle] = true;

            DB::table($table)->where('id', $brand->id)->update([
                'handle' => $handle,
                'status' => 'active',
            ]);
        });

        Schema::table($table, function ($blueprint) {
            $blueprint->string('handle')->nullable(false)->change();
            $blueprint->unique('handle');
        });
    }
};
