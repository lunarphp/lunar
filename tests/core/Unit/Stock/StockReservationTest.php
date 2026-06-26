<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\StockReservation;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true, 'decimal_places' => 2]);
    Location::factory()->create(['default' => true]);
});

test('reserving stock reduces availability', function () {
    $variant = ProductVariant::factory()->inStock(10)->create();

    $reservation = $variant->reserveStock(3);

    expect($variant->fresh())
        ->stock_reserved->toBe(3)
        ->stock_available->toBe(7)
        ->and($reservation->is_active)->toBeTrue();
});

test('releasing a reservation restores availability', function () {
    $variant = ProductVariant::factory()->inStock(10)->create();
    $reservation = $variant->reserveStock(3);

    $reservation->release();

    expect($variant->fresh())
        ->stock_reserved->toBe(0)
        ->stock_available->toBe(10)
        ->and($reservation->fresh()->released_at)->not->toBeNull();
});

test('committing a reservation frees the reserved quantity and stamps committed_at', function () {
    $variant = ProductVariant::factory()->inStock(10)->create();
    $reservation = $variant->reserveStock(3);

    $reservation->commit();

    expect($variant->fresh()->stock_reserved)->toBe(0)
        ->and($reservation->fresh()->committed_at)->not->toBeNull()
        ->and($reservation->fresh()->released_at)->toBeNull();
});

test('release is a no-op once committed', function () {
    $variant = ProductVariant::factory()->inStock(10)->create();
    $reservation = $variant->reserveStock(3);
    $reservation->commit();

    $reservation->release();

    expect($reservation->fresh()->released_at)->toBeNull();
});

test('an already-expired hold does not reserve stock', function () {
    $variant = ProductVariant::factory()->inStock(10)->create();

    $variant->reserveStock(3, expiresAt: now()->subMinute());

    expect($variant->fresh())
        ->stock_reserved->toBe(0)
        ->stock_available->toBe(10);
});

test('the release-expired command releases lapsed holds but leaves live ones', function () {
    $variant = ProductVariant::factory()->inStock(10)->create();

    $expired = StockReservation::factory()->create([
        'product_variant_id' => $variant->id,
        'quantity' => 3,
        'expires_at' => now()->subHour(),
    ]);
    $live = StockReservation::factory()->create([
        'product_variant_id' => $variant->id,
        'quantity' => 2,
        'expires_at' => now()->addHour(),
    ]);

    $this->artisan('lunar:stock:release-expired')->assertSuccessful();

    expect($expired->fresh()->released_at)->not->toBeNull()
        ->and($live->fresh()->released_at)->toBeNull()
        ->and($variant->fresh()->stock_reserved)->toBe(2);
});

test('reconcile rebuilds the reserved rollup from active reservations', function () {
    $variant = ProductVariant::factory()->inStock(10)->create();
    StockReservation::factory()->create(['product_variant_id' => $variant->id, 'quantity' => 4]);

    $variant->forceFill(['stock_reserved' => 999, 'stock_available' => -999])->save();

    $this->artisan('lunar:stock:reconcile')->assertSuccessful();

    expect($variant->fresh())
        ->stock_reserved->toBe(4)
        ->stock_available->toBe(6);
});
