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
const SHIPLINE_UPG_PREFIX = 'upgshipline_';

beforeEach(function () {
    config(['lunar.database.table_prefix' => SHIPLINE_UPG_PREFIX]);
});

afterEach(function () {
    Schema::dropIfExists(SHIPLINE_UPG_PREFIX.'order_lines');
});

function shippingMorphMigration(): object
{
    $path = glob(dirname(__DIR__, 3).'/packages/upgrade/database/migrations/*null_shipping_line_purchasable_morph.php');

    return require $path[0];
}

/**
 * Stand up the v1-shaped `order_lines` table: the purchasable morph is NOT NULL
 * (which is why v1 wrote the fake `ShippingOption` morph onto shipping lines).
 */
function simulateV1OrderLines(): void
{
    Schema::create(SHIPLINE_UPG_PREFIX.'order_lines', function (Blueprint $table) {
        $table->id();
        $table->string('type');
        $table->morphs('purchasable');
        $table->string('description');
        $table->timestamps();
    });

    DB::table(SHIPLINE_UPG_PREFIX.'order_lines')->insert([
        ['id' => 1, 'type' => 'shipping', 'purchasable_type' => 'Lunar\\DataTypes\\ShippingOption', 'purchasable_id' => 1, 'description' => 'Standard Delivery', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 2, 'type' => 'physical', 'purchasable_type' => 'Lunar\\Models\\ProductVariant', 'purchasable_id' => 5, 'description' => 'Blue Tee', 'created_at' => now(), 'updated_at' => now()],
    ]);
}

test('it makes the morph nullable and nulls it on shipping lines', function () {
    simulateV1OrderLines();

    shippingMorphMigration()->up();

    $shipping = DB::table(SHIPLINE_UPG_PREFIX.'order_lines')->find(1);
    expect($shipping->purchasable_type)->toBeNull()
        ->and($shipping->purchasable_id)->toBeNull()
        ->and($shipping->description)->toBe('Standard Delivery');   // snapshot untouched

    $physical = DB::table(SHIPLINE_UPG_PREFIX.'order_lines')->find(2);
    expect($physical->purchasable_type)->toBe('Lunar\\Models\\ProductVariant')
        ->and((int) $physical->purchasable_id)->toBe(5);
});

test('it is a no-op on an already-migrated database', function () {
    Schema::create(SHIPLINE_UPG_PREFIX.'order_lines', function (Blueprint $table) {
        $table->id();
        $table->string('type');
        $table->nullableMorphs('purchasable');
        $table->timestamps();
    });

    DB::table(SHIPLINE_UPG_PREFIX.'order_lines')->insert([
        'id' => 1, 'type' => 'shipping', 'purchasable_type' => null, 'purchasable_id' => null, 'created_at' => now(), 'updated_at' => now(),
    ]);

    shippingMorphMigration()->up();

    $line = DB::table(SHIPLINE_UPG_PREFIX.'order_lines')->find(1);
    expect($line->purchasable_type)->toBeNull();
});
