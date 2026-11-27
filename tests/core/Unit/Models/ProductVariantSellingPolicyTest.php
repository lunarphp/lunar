<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Contracts\Actions\Products\ResolvesInventory;
use Lunar\Core\DataObjects\VariantInventory;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

/**
 * A variant with 30 available and 50 incoming, so each flag's effect on the
 * figure is visible.
 */
function variantWithIncoming(ProductVariant $variant): ProductVariant
{
    $variant->forceFill(['stock_incoming' => 50])->save();

    return $variant->refresh();
}

test('a new variant continues selling and ignores incoming stock', function () {
    $variant = ProductVariant::factory()->create()->refresh();

    expect($variant->continue_selling)->toBeTrue()
        ->and($variant->sell_against_incoming)->toBeFalse();
});

test('a variant that continues selling reports available stock only', function () {
    $variant = variantWithIncoming(ProductVariant::factory()->inStock(30)->create());

    expect($variant->getTotalInventory())->toBe(30)
        ->and($variant->canBeFulfilledAtQuantity(9999))->toBeTrue();
});

test('a stock-bound variant sells only what is available', function () {
    $variant = variantWithIncoming(ProductVariant::factory()->stockBound()->inStock(30)->create());

    expect($variant->getTotalInventory())->toBe(30)
        ->and($variant->canBeFulfilledAtQuantity(30))->toBeTrue()
        ->and($variant->canBeFulfilledAtQuantity(31))->toBeFalse();
});

test('a variant selling against incoming adds incoming stock to available', function () {
    $variant = variantWithIncoming(ProductVariant::factory()->sellsAgainstIncoming()->inStock(30)->create());

    expect($variant->getTotalInventory())->toBe(80)
        ->and($variant->canBeFulfilledAtQuantity(80))->toBeTrue()
        ->and($variant->canBeFulfilledAtQuantity(81))->toBeFalse();
});

test('continuing to sell counts incoming stock when selling against it', function () {
    $variant = variantWithIncoming(ProductVariant::factory()->inStock(30)->create([
        'sell_against_incoming' => true,
    ]));

    expect($variant->getTotalInventory())->toBe(80)
        ->and($variant->canBeFulfilledAtQuantity(9999))->toBeTrue();
});

test('inventory answers come from the ResolvesInventory seam', function () {
    $variant = ProductVariant::factory()->stockBound()->inStock(1)->create();

    app()->bind(ResolvesInventory::class, fn () => new class implements ResolvesInventory
    {
        public function execute(ProductVariant $variant): VariantInventory
        {
            return new VariantInventory(available: 7, unlimited: false);
        }
    });

    expect($variant->refresh()->getTotalInventory())->toBe(7)
        ->and($variant->canBeFulfilledAtQuantity(7))->toBeTrue()
        ->and($variant->canBeFulfilledAtQuantity(8))->toBeFalse();
});
