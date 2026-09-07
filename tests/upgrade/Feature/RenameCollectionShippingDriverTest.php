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
const SHIPDRIVER_UPG_PREFIX = 'upgshipdriver_';

beforeEach(function () {
    config(['lunar.database.table_prefix' => SHIPDRIVER_UPG_PREFIX]);
});

afterEach(function () {
    Schema::dropIfExists(SHIPDRIVER_UPG_PREFIX.'shipping_methods');
});

function shippingDriverMigration(): object
{
    $path = glob(dirname(__DIR__, 3).'/packages/upgrade/database/migrations/*rename_collection_shipping_driver.php');

    return require $path[0];
}

/**
 * Stand up a v1-shaped `shipping_methods` table with rows on each driver key.
 */
function simulateV1ShippingMethods(): void
{
    Schema::create(SHIPDRIVER_UPG_PREFIX.'shipping_methods', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('driver');
        $table->timestamps();
    });

    DB::table(SHIPDRIVER_UPG_PREFIX.'shipping_methods')->insert([
        ['id' => 1, 'name' => 'Click & Collect', 'driver' => 'collection', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 2, 'name' => 'Standard Delivery', 'driver' => 'ship-by', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 3, 'name' => 'Free Shipping', 'driver' => 'free-shipping', 'created_at' => now(), 'updated_at' => now()],
    ]);
}

test('it renames the collection driver to pickup and leaves the rest alone', function () {
    simulateV1ShippingMethods();

    shippingDriverMigration()->up();

    expect(DB::table(SHIPDRIVER_UPG_PREFIX.'shipping_methods')->find(1)->driver)->toBe('pickup')
        ->and(DB::table(SHIPDRIVER_UPG_PREFIX.'shipping_methods')->find(2)->driver)->toBe('ship-by')
        ->and(DB::table(SHIPDRIVER_UPG_PREFIX.'shipping_methods')->find(3)->driver)->toBe('free-shipping');
});

test('it is idempotent on re-run', function () {
    simulateV1ShippingMethods();

    shippingDriverMigration()->up();
    shippingDriverMigration()->up();

    expect(DB::table(SHIPDRIVER_UPG_PREFIX.'shipping_methods')->where('driver', 'pickup')->count())->toBe(1)
        ->and(DB::table(SHIPDRIVER_UPG_PREFIX.'shipping_methods')->where('driver', 'collection')->count())->toBe(0);
});

test('it is a no-op when the table does not exist', function () {
    shippingDriverMigration()->up();

    expect(Schema::hasTable(SHIPDRIVER_UPG_PREFIX.'shipping_methods'))->toBeFalse();
});
