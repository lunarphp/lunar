<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Enums\SellingPolicy;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('selling_policy casts to the enum', function () {
    $variant = ProductVariant::factory()->create(['selling_policy' => 'in_stock']);

    expect($variant->refresh()->selling_policy)->toBe(SellingPolicy::InStock);
});

test('always reports physical stock only and never adds backorder', function () {
    $variant = ProductVariant::factory()->inStock(30)->create([
        'selling_policy' => SellingPolicy::Always,
        'backorder' => 50,
    ]);

    // The bug fix: Always no longer inflates the figure with the backorder allowance.
    expect($variant->refresh()->getTotalInventory())->toBe(30);
});

test('in_stock reports physical stock only', function () {
    $variant = ProductVariant::factory()->inStock(30)->create([
        'selling_policy' => SellingPolicy::InStock,
        'backorder' => 50,
    ]);

    expect($variant->refresh()->getTotalInventory())->toBe(30);
});

test('in_stock_or_on_backorder adds the backorder allowance', function () {
    $variant = ProductVariant::factory()->inStock(30)->create([
        'selling_policy' => SellingPolicy::InStockOrOnBackorder,
        'backorder' => 50,
    ]);

    expect($variant->refresh()->getTotalInventory())->toBe(80);
});

test('always can be fulfilled at any quantity beyond its inventory', function () {
    $variant = ProductVariant::factory()->inStock(5)->create([
        'selling_policy' => SellingPolicy::Always,
        'backorder' => 0,
    ]);

    expect($variant->refresh()->canBeFulfilledAtQuantity(9999))->toBeTrue();
});

test('in_stock cannot be fulfilled beyond available stock', function () {
    $variant = ProductVariant::factory()->inStock(5)->create([
        'selling_policy' => SellingPolicy::InStock,
        'backorder' => 0,
    ]);

    expect($variant->refresh()->canBeFulfilledAtQuantity(6))->toBeFalse();
    expect($variant->refresh()->canBeFulfilledAtQuantity(5))->toBeTrue();
});
