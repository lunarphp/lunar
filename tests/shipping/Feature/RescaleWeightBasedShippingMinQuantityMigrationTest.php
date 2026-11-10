<?php

use Illuminate\Support\Facades\DB;
use Lunar\Models\Currency;
use Lunar\Models\TaxClass;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Shipping\Models\ShippingRate;
use Lunar\Shipping\Models\ShippingZone;
use Lunar\Tests\Shipping\TestCase;

use function Pest\Laravel\artisan;

uses(TestCase::class)->group('migrations');

const RESCALE_MIGRATION = __DIR__.'/../../../packages/table-rate-shipping/database/migrations/2026_05_18_100000_rescale_weight_based_shipping_min_quantity.php';

test('rescales legacy weight-based shipping min_quantity from kg × 100 to raw kg', function () {
    artisan('migrate');
    // Roll back this specific migration — `--step 1` would silently target
    // whichever migration happens to be newest.
    artisan('migrate:rollback', [
        '--path' => realpath(RESCALE_MIGRATION),
        '--realpath' => true,
    ]);

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

    artisan('migrate');

    expect((int) DB::table("{$prefix}prices")->where('id', $weightBaseId)->value('min_quantity'))->toBe(1);
    expect((int) DB::table("{$prefix}prices")->where('id', $weightTier5KgId)->value('min_quantity'))->toBe(5);
    expect((int) DB::table("{$prefix}prices")->where('id', $weightTier10KgId)->value('min_quantity'))->toBe(10);
    expect((int) DB::table("{$prefix}prices")->where('id', $cartTotalTierId)->value('min_quantity'))->toBe(5000);

    // Idempotency: rolling back and re-applying must be a no-op because the
    // rescaled values (5, 10) don't match the `min_quantity % 100 = 0` guard.
    artisan('migrate:rollback', [
        '--path' => realpath(RESCALE_MIGRATION),
        '--realpath' => true,
    ]);
    artisan('migrate');

    expect((int) DB::table("{$prefix}prices")->where('id', $weightTier5KgId)->value('min_quantity'))->toBe(5);
    expect((int) DB::table("{$prefix}prices")->where('id', $weightTier10KgId)->value('min_quantity'))->toBe(10);
    expect((int) DB::table("{$prefix}prices")->where('id', $cartTotalTierId)->value('min_quantity'))->toBe(5000);

    // No RefreshDatabase, so factory rows are committed — clean them up to
    // avoid leaking into subsequent tests.
    DB::table("{$prefix}prices")->whereIn('id', [
        $weightBaseId, $weightTier5KgId, $weightTier10KgId, $cartTotalTierId,
    ])->delete();
    DB::table("{$prefix}shipping_rates")
        ->whereIn('id', [$weightRate->id, $cartTotalRate->id])->delete();
    DB::table("{$prefix}shipping_methods")
        ->whereIn('id', [$weightMethod->id, $cartTotalMethod->id])->delete();
    DB::table("{$prefix}shipping_zones")->where('id', $zone->id)->delete();
    DB::table("{$prefix}tax_classes")->delete();
    DB::table("{$prefix}currencies")->delete();
});

test('migration is a no-op when there are no weight-based shipping methods', function () {
    artisan('migrate');
    artisan('migrate:rollback', [
        '--path' => realpath(RESCALE_MIGRATION),
        '--realpath' => true,
    ]);

    $currency = Currency::factory()->create(['default' => true]);
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
        'currency_id' => $currency->id,
        'price' => 500,
        'min_quantity' => 5000,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    artisan('migrate');

    expect((int) DB::table("{$prefix}prices")->where('id', $priceId)->value('min_quantity'))->toBe(5000);

    DB::table("{$prefix}prices")->where('id', $priceId)->delete();
    DB::table("{$prefix}shipping_rates")->where('id', $cartTotalRate->id)->delete();
    DB::table("{$prefix}shipping_methods")->where('id', $cartTotalMethod->id)->delete();
    DB::table("{$prefix}shipping_zones")->where('id', $zone->id)->delete();
    DB::table("{$prefix}tax_classes")->delete();
    DB::table("{$prefix}currencies")->delete();
});
