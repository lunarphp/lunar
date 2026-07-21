<?php

use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * v1 → v2 upgrade data step (spec 0057): add `product_variants.enabled`.
 *
 * v2 gives variants a merchant availability toggle — a disabled variant is
 * never purchasable regardless of product status or stock. v1 had no such
 * column; every existing variant is marked enabled so storefronts keep all
 * variants sellable after the upgrade (the column default covers the
 * backfill).
 *
 * Guarded so re-runs and already-v2 databases are no-ops. There is no `down()`:
 * upgrade-package data migrations are one-way — recover from a backup if an
 * upgrade fails rather than attempting to reverse a data move.
 */
return new class extends Migration
{
    public function up(): void
    {
        $table = $this->prefix.'product_variants';

        if (! Schema::hasTable($table) || Schema::hasColumn($table, 'enabled')) {
            return;
        }

        Schema::table($table, function ($blueprint) {
            $blueprint->boolean('enabled')->default(true)->index();
        });
    }
};
