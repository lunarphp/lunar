<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Products\DuplicateProduct;
use Lunar\Core\Enums\StockMovementType;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\StockLevel;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true, 'decimal_places' => 2]);
});

test('duplicates a product as a draft', function () {
    $product = Product::factory()->create([
        'status' => 'published',
        'name' => collect(['en' => 'Widget', 'fr' => 'Bidule']),
    ]);

    ProductVariant::factory()->create(['product_id' => $product->id, 'sku' => 'WIDGET-1']);

    $duplicate = app(DuplicateProduct::class)->execute($product);

    expect($duplicate)->toBeInstanceOf(Product::class);
    expect($duplicate->id)->not->toBe($product->id);
    expect((string) $duplicate->status)->toBe('draft');
    expect($duplicate->variants)->toHaveCount(1);
    expect($duplicate->variants->first()->sku)->toBe('WIDGET-1-copy');

    $suffix = trim((string) __('lunar::products.duplicate.name_suffix'));

    expect($duplicate->translate('name'))->toBe("Widget {$suffix}");
    expect($duplicate->name->get('fr'))->toBe("Bidule {$suffix}");
});

test('the duplicate starts with no stock while the source keeps its own', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'backorder' => 5]);
    $location = Location::factory()->create(['default' => true]);

    $variant->adjustStock($location, 9, StockMovementType::Received);

    // The global commitment and reservation counters belong to the source's
    // orders and reservations, none of which refer to the duplicate.
    $variant->forceFill([
        'stock_incoming' => 4,
        'stock_committed' => 2,
        'stock_reserved' => 1,
        'stock_available' => 6,
    ])->save();

    $duplicate = app(DuplicateProduct::class)->execute($product);
    $copy = $duplicate->variants->first();

    expect($copy->fresh())
        ->stock_on_hand->toBe(0)
        ->stock_incoming->toBe(0)
        ->stock_committed->toBe(0)
        ->stock_reserved->toBe(0)
        ->stock_unavailable->toBe(0)
        ->stock_available->toBe(0)
        ->backorder->toBe(5);

    expect(StockLevel::where('product_variant_id', $copy->id)->exists())->toBeFalse();

    expect($variant->fresh())
        ->stock_on_hand->toBe(9)
        ->stock_incoming->toBe(4)
        ->stock_committed->toBe(2)
        ->stock_reserved->toBe(1)
        ->stock_available->toBe(6);

    expect(StockLevel::where('product_variant_id', $variant->id)->value('on_hand'))->toBe(9);
});
