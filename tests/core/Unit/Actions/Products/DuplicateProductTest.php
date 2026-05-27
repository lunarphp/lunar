<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Products\DuplicateProduct;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
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
    expect($duplicate->status)->toBe('draft');
    expect($duplicate->variants)->toHaveCount(1);
    expect($duplicate->variants->first()->sku)->toBe('WIDGET-1-copy');

    $suffix = trim((string) __('lunar::products.duplicate.name_suffix'));

    expect($duplicate->translate('name'))->toBe("Widget {$suffix}");
    expect($duplicate->name->get('fr'))->toBe("Bidule {$suffix}");
});
