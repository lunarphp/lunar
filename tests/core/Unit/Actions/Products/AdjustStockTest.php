<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Products\AdjustStock;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true, 'decimal_places' => 2]);
    Location::factory()->create(['default' => true]);
});

test('adds stock using a positive delta', function () {
    $variant = ProductVariant::factory()->inStock(5)->create();

    app(AdjustStock::class)->execute($variant, 3, 'restock');

    expect($variant->fresh()->stock_on_hand)->toBe(8);
});

test('removes stock using a negative delta', function () {
    $variant = ProductVariant::factory()->inStock(5)->create();

    app(AdjustStock::class)->execute($variant, -2);

    expect($variant->fresh()->stock_on_hand)->toBe(3);
});

test('allows a free-form adjustment below zero', function () {
    $variant = ProductVariant::factory()->inStock(1)->create();

    app(AdjustStock::class)->execute($variant, -5);

    expect($variant->fresh()->stock_on_hand)->toBe(-4);
});

test('is a no-op when delta is zero', function () {
    $variant = ProductVariant::factory()->inStock(5)->create();

    app(AdjustStock::class)->execute($variant, 0);

    expect($variant->fresh()->stock_on_hand)->toBe(5);
});
