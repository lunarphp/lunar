<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Enums\StockMovementType;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\StockLevel;
use Lunar\Core\Models\StockMovement;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true, 'decimal_places' => 2]);
});

test('records a movement, creating the level and refreshing the rollup', function () {
    $variant = ProductVariant::factory()->create();
    $location = Location::factory()->create(['default' => true]);

    $movement = $variant->adjustStock($location, 5, StockMovementType::Received);

    expect($movement)->toBeInstanceOf(StockMovement::class)
        ->and($movement->quantity)->toBe(5)
        ->and($movement->type)->toBe(StockMovementType::Received);

    $level = StockLevel::where('product_variant_id', $variant->id)
        ->where('location_id', $location->id)
        ->first();

    expect($level->on_hand)->toBe(5)
        ->and($level->available)->toBe(5);

    expect($variant->fresh())
        ->stock_on_hand->toBe(5)
        ->stock_available->toBe(5);
});

test('accumulates successive movements on the same level', function () {
    $variant = ProductVariant::factory()->create();
    $location = Location::factory()->create(['default' => true]);

    $variant->adjustStock($location, 5, StockMovementType::Received);
    $variant->adjustStock($location, 3, StockMovementType::Received);

    expect(StockLevel::where('product_variant_id', $variant->id)->count())->toBe(1)
        ->and($variant->fresh()->stock_on_hand)->toBe(8)
        ->and(StockMovement::where('product_variant_id', $variant->id)->count())->toBe(2);
});

test('sums on_hand across locations into the rollup', function () {
    $variant = ProductVariant::factory()->create();
    $a = Location::factory()->create(['default' => true]);
    $b = Location::factory()->create();

    $variant->adjustStock($a, 5, StockMovementType::Received);
    $variant->adjustStock($b, 7, StockMovementType::Received);

    expect($variant->fresh()->stock_on_hand)->toBe(12)
        ->and($variant->fresh()->stock_available)->toBe(12);
});

test('allows on_hand to go negative and records a signed movement', function () {
    $variant = ProductVariant::factory()->create();
    $location = Location::factory()->create(['default' => true]);

    $variant->adjustStock($location, 2, StockMovementType::Received);
    $variant->adjustStock($location, -5, StockMovementType::Shipped);

    expect($variant->fresh()->stock_on_hand)->toBe(-3);
});

test('available subtracts committed and unavailable', function () {
    $variant = ProductVariant::factory()->create();
    $location = Location::factory()->create(['default' => true]);

    $variant->adjustStock($location, 10, StockMovementType::Received);

    StockLevel::where('product_variant_id', $variant->id)
        ->update(['committed' => 4, 'unavailable' => 2]);

    $level = StockLevel::where('product_variant_id', $variant->id)->first();

    expect($level->available)->toBe(4);
});

test('records the source and causer on the movement', function () {
    $variant = ProductVariant::factory()->create();
    $location = Location::factory()->create(['default' => true]);

    $source = Location::factory()->create();

    $movement = $variant->adjustStock($location, 1, StockMovementType::Adjustment, source: $source, note: 'manual count');

    expect($movement->source_id)->toBe($source->id)
        ->and($movement->note)->toBe('manual count');
});
