<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Lunar\Models\Currency;
use Lunar\Models\TaxClass;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Shipping\Models\ShippingRate;
use Lunar\Shipping\Models\ShippingZone;
use Lunar\Tests\Shipping\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

function loadRescaleWeightMigration(): object
{
    return require __DIR__.'/../../../packages/table-rate-shipping/database/migrations/2026_05_18_100000_rescale_weight_based_shipping_min_quantity.php';
}

test('migration rescales weight-based shipping min_quantity from legacy x100 storage to raw kg', function () {
    $currency = Currency::factory()->create(['default' => true]);
    TaxClass::factory()->create(['default' => true]);
    $zone = ShippingZone::factory()->create(['type' => 'countries']);

    $weightMethod = ShippingMethod::factory()->create([
        'driver' => 'ship-by',
        'data' => ['charge_by' => 'weight'],
    ]);
    $weightRate = ShippingRate::factory()->create([
        'shipping_method_id' => $weightMethod->id,
        'shipping_zone_id' => $zone->id,
    ]);

    $cartTotalMethod = ShippingMethod::factory()->create([
        'driver' => 'ship-by',
        'data' => ['charge_by' => 'cart_total'],
    ]);
    $cartTotalRate = ShippingRate::factory()->create([
        'shipping_method_id' => $cartTotalMethod->id,
        'shipping_zone_id' => $zone->id,
    ]);

    $prefix = config('lunar.database.table_prefix');

    $weightBaseId = DB::table("{$prefix}prices")->insertGetId([
        'priceable_type' => 'shipping_rate',
        'priceable_id' => $weightRate->id,
        'currency_id' => $currency->id,
        'price' => 1000,
        'min_quantity' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $weightTier5KgId = DB::table("{$prefix}prices")->insertGetId([
        'priceable_type' => 'shipping_rate',
        'priceable_id' => $weightRate->id,
        'currency_id' => $currency->id,
        'price' => 600,
        'min_quantity' => 500,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $weightTier10KgId = DB::table("{$prefix}prices")->insertGetId([
        'priceable_type' => 'shipping_rate',
        'priceable_id' => $weightRate->id,
        'currency_id' => $currency->id,
        'price' => 200,
        'min_quantity' => 1000,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $cartTotalTierId = DB::table("{$prefix}prices")->insertGetId([
        'priceable_type' => 'shipping_rate',
        'priceable_id' => $cartTotalRate->id,
        'currency_id' => $currency->id,
        'price' => 500,
        'min_quantity' => 5000,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    loadRescaleWeightMigration()->up();

    expect((int) DB::table("{$prefix}prices")->where('id', $weightBaseId)->value('min_quantity'))->toBe(1);
    expect((int) DB::table("{$prefix}prices")->where('id', $weightTier5KgId)->value('min_quantity'))->toBe(5);
    expect((int) DB::table("{$prefix}prices")->where('id', $weightTier10KgId)->value('min_quantity'))->toBe(10);
    expect((int) DB::table("{$prefix}prices")->where('id', $cartTotalTierId)->value('min_quantity'))->toBe(5000);
});

test('migration is applied exactly once by the migrator after artisan migrate', function () {
    expect(DB::table('migrations')
        ->where('migration', '2026_05_18_100000_rescale_weight_based_shipping_min_quantity')
        ->count())
        ->toBe(1);
});

test('migration is a no-op when there are no weight-based shipping methods', function () {
    Currency::factory()->create(['default' => true]);
    TaxClass::factory()->create(['default' => true]);
    $zone = ShippingZone::factory()->create(['type' => 'countries']);

    $cartTotalMethod = ShippingMethod::factory()->create([
        'driver' => 'ship-by',
        'data' => ['charge_by' => 'cart_total'],
    ]);
    $cartTotalRate = ShippingRate::factory()->create([
        'shipping_method_id' => $cartTotalMethod->id,
        'shipping_zone_id' => $zone->id,
    ]);

    $prefix = config('lunar.database.table_prefix');

    $priceId = DB::table("{$prefix}prices")->insertGetId([
        'priceable_type' => 'shipping_rate',
        'priceable_id' => $cartTotalRate->id,
        'currency_id' => Currency::getDefault()->id,
        'price' => 500,
        'min_quantity' => 5000,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    loadRescaleWeightMigration()->up();

    expect((int) DB::table("{$prefix}prices")->where('id', $priceId)->value('min_quantity'))->toBe(5000);
});
