<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Enums\StockMovementType;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\StockLevel;
use Lunar\Core\Models\StockMovement;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('sets incoming stock absolutely, creating the level and refreshing the rollup', function () {
    $variant = ProductVariant::factory()->create();
    $location = Location::factory()->create(['default' => true]);

    $variant->setIncomingStock($location, 12);
    $level = $variant->setIncomingStock($location, 5);

    expect($level)->toBeInstanceOf(StockLevel::class)
        ->and($level->incoming)->toBe(5)
        ->and($variant->fresh()->stock_incoming)->toBe(5);
});

test('rolls incoming stock up across locations without touching on-hand stock', function () {
    $variant = ProductVariant::factory()->create();
    $warehouse = Location::factory()->create(['default' => true]);
    $store = Location::factory()->create();

    $variant->adjustStock($warehouse, 8, StockMovementType::Received);
    $variant->setIncomingStock($warehouse, 3);
    $variant->setIncomingStock($store, 4);

    expect($variant->fresh())
        ->stock_incoming->toBe(7)
        ->stock_on_hand->toBe(8)
        ->stock_available->toBe(8);

    // Incoming is not part of the on-hand ledger.
    expect(StockMovement::where('product_variant_id', $variant->id)->count())->toBe(1);
});

test('clears incoming stock with zero', function () {
    $variant = ProductVariant::factory()->create();
    $location = Location::factory()->create(['default' => true]);

    $variant->setIncomingStock($location, 6);
    $variant->setIncomingStock($location, 0);

    expect($variant->fresh()->stock_incoming)->toBe(0);
});

test('rejects negative incoming stock', function () {
    $variant = ProductVariant::factory()->create();
    $location = Location::factory()->create(['default' => true]);

    $variant->setIncomingStock($location, 6);

    expect(fn () => $variant->setIncomingStock($location, -1))
        ->toThrow(InvalidArgumentException::class);

    expect($variant->fresh()->stock_incoming)->toBe(6);
});

test('incoming stock becomes sellable for a variant selling against it', function () {
    $variant = ProductVariant::factory()->sellsAgainstIncoming()->create();
    $location = Location::factory()->create(['default' => true]);

    expect($variant->canBeFulfilledAtQuantity(1))->toBeFalse();

    $variant->setIncomingStock($location, 2);

    expect($variant->fresh()->canBeFulfilledAtQuantity(2))->toBeTrue()
        ->and($variant->fresh()->canBeFulfilledAtQuantity(3))->toBeFalse();
});
