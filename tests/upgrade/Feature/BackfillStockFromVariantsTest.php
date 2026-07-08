<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Tests\Upgrade\TestCase;

uses(TestCase::class);

/**
 * Isolated prefix so this stands up its own throwaway schema without touching
 * the shared lunar_* tables.
 */
const SPEC0038_PREFIX = 'upg0038_';

beforeEach(function () {
    config(['lunar.database.table_prefix' => SPEC0038_PREFIX]);
});

afterEach(function () {
    foreach (['stock_movements', 'stock_levels', 'product_variants', 'locations'] as $table) {
        Schema::dropIfExists(SPEC0038_PREFIX.$table);
    }
});

function stockMigration(): object
{
    $path = glob(dirname(__DIR__, 3).'/packages/upgrade/database/migrations/*backfill_stock_from_variants.php');

    return require $path[0];
}

/**
 * Stand up the v1-shaped tables: a `product_variants` table carrying the flat
 * `stock` column. v1 has no `locations` table (or any of the stock tables) —
 * the migration must create them itself.
 */
function simulateV1Variants(): void
{
    Schema::create(SPEC0038_PREFIX.'product_variants', function (Blueprint $table) {
        $table->id();
        $table->integer('stock')->default(0);
        $table->integer('backorder')->default(0);
        $table->string('purchasable')->default('always');
        $table->timestamps();
    });

    DB::table(SPEC0038_PREFIX.'product_variants')->insert([
        ['id' => 1, 'stock' => 10, 'backorder' => 5, 'purchasable' => 'in_stock', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 2, 'stock' => 0, 'backorder' => 0, 'purchasable' => 'always', 'created_at' => now(), 'updated_at' => now()],
    ]);
}

test('it backfills stock levels and an opening movement from the v1 stock column', function () {
    simulateV1Variants();

    stockMigration()->up();

    // The flat column is gone.
    expect(Schema::hasColumn(SPEC0038_PREFIX.'product_variants', 'stock'))->toBeFalse();

    // v1 has no locations table: the migration creates it and seeds a default.
    $location = DB::table(SPEC0038_PREFIX.'locations')->first();
    expect($location->handle)->toBe('default')
        ->and((bool) $location->default)->toBeTrue();

    // Variant 1 (stock 10): a level at the default location, an opening movement, the rollup.
    $level = DB::table(SPEC0038_PREFIX.'stock_levels')->where('product_variant_id', 1)->first();
    expect((int) $level->on_hand)->toBe(10)
        ->and((int) $level->location_id)->toBe((int) $location->id);

    $movement = DB::table(SPEC0038_PREFIX.'stock_movements')->where('product_variant_id', 1)->first();
    expect((int) $movement->quantity)->toBe(10)
        ->and($movement->type)->toBe('opening_balance');

    $variant = DB::table(SPEC0038_PREFIX.'product_variants')->find(1);
    expect((int) $variant->stock_on_hand)->toBe(10)
        ->and((int) $variant->stock_available)->toBe(10)
        ->and((int) $variant->backorder)->toBe(5)          // selling policy untouched
        ->and($variant->purchasable)->toBe('in_stock');

    // Variant 2 (stock 0): no level, no movement.
    expect(DB::table(SPEC0038_PREFIX.'stock_levels')->where('product_variant_id', 2)->count())->toBe(0)
        ->and(DB::table(SPEC0038_PREFIX.'stock_movements')->where('product_variant_id', 2)->count())->toBe(0);
});

test('it reuses an existing default location instead of creating one', function () {
    simulateV1Variants();

    Schema::create(SPEC0038_PREFIX.'locations', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('handle');
        $table->boolean('default')->default(false);
        $table->timestamps();
    });

    DB::table(SPEC0038_PREFIX.'locations')->insert([
        'id' => 7, 'name' => 'Main', 'handle' => 'main', 'default' => true, 'created_at' => now(), 'updated_at' => now(),
    ]);

    stockMigration()->up();

    expect(DB::table(SPEC0038_PREFIX.'locations')->count())->toBe(1)
        ->and((int) DB::table(SPEC0038_PREFIX.'stock_levels')->where('product_variant_id', 1)->value('location_id'))->toBe(7);
});

test('it is a no-op on an already-migrated database', function () {
    Schema::create(SPEC0038_PREFIX.'product_variants', function (Blueprint $table) {
        $table->id();
        $table->integer('stock_on_hand')->default(0);
        $table->timestamps();
    });

    stockMigration()->up();

    expect(Schema::hasTable(SPEC0038_PREFIX.'stock_levels'))->toBeFalse();
});
