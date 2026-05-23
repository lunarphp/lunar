<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Products\AdjustStock;
use Lunar\Core\Exceptions\ProductActionException;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true, 'decimal_places' => 2]);
});

test('adds stock using a positive delta', function () {
    $variant = ProductVariant::factory()->create(['stock' => 5]);

    AdjustStock::run($variant, 3, 'restock');

    expect($variant->fresh()->stock)->toBe(8);
});

test('removes stock using a negative delta', function () {
    $variant = ProductVariant::factory()->create(['stock' => 5]);

    AdjustStock::run($variant, -2);

    expect($variant->fresh()->stock)->toBe(3);
});

test('rejects deltas that would push stock below zero', function () {
    $variant = ProductVariant::factory()->create(['stock' => 1]);

    AdjustStock::run($variant, -5);
})->throws(ProductActionException::class);

test('is a no-op when delta is zero', function () {
    $variant = ProductVariant::factory()->create(['stock' => 5]);

    AdjustStock::run($variant, 0);

    expect($variant->fresh()->stock)->toBe(5);
});
