<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * v1 → v2 upgrade data step: introduce the promotions campaign layer.
 *
 * v2 groups discounts under a `Promotion` campaign via a nullable
 * `discounts.promotion_id`. v1 has no promotion concept, so this creates the
 * `promotions` table and adds the nullable column; every existing discount
 * keeps `promotion_id = null` (standalone) and works unchanged. Merchants group
 * discounts into campaigns afterwards in the admin — there is no data to
 * backfill.
 *
 * Guarded so re-runs and already-v2 databases are no-ops. There is no `down()`:
 * upgrade-package data migrations are one-way — recover from a backup if an
 * upgrade fails rather than attempting to reverse a data move.
 */
return new class extends Migration
{
    public function up(): void
    {
        $promotions = $this->prefix.'promotions';

        if (! Schema::hasTable($promotions)) {
            Schema::create($promotions, function (Blueprint $table) {
                $table->id();
                $table->ulid('public_id')->unique();
                $table->jsonb('name');
                $table->jsonb('description')->nullable();
                $table->string('handle')->unique();
                $table->dateTime('starts_at')->nullable()->index();
                $table->dateTime('ends_at')->nullable()->index();
                $table->timestamps();
            });
        }

        $discounts = $this->prefix.'discounts';

        if (Schema::hasTable($discounts) && ! Schema::hasColumn($discounts, 'promotion_id')) {
            Schema::table($discounts, function (Blueprint $table) {
                $table->foreignId('promotion_id')->nullable()->index()->after('public_id');
            });
        }
    }
};
