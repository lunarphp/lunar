<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Products\CreateProduct;
use Lunar\Core\Models\ProductType;
use Lunar\Core\Models\TaxClass;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('creates a product with an initial variant using the type default tax class', function () {
    TaxClass::factory()->create(['default' => true]);
    $taxClass = TaxClass::factory()->create();
    $productType = ProductType::factory()->create(['default_tax_class_id' => $taxClass->id]);

    $product = app(CreateProduct::class)->execute([
        'name' => ['en' => 'Coffee Grinder'],
        'product_type_id' => $productType->id,
        'status' => 'draft',
    ]);

    expect($product->translate('name'))->toBe('Coffee Grinder')
        ->and((string) $product->status)->toBe('draft')
        ->and($product->variants)->toHaveCount(1)
        ->and($product->variants->first()->tax_class_id)->toBe($taxClass->id)
        ->and($product->variants->first()->enabled)->toBeTrue()
        ->and($product->variants->first()->sku)->toBeNull();
});

test('falls back to the store default tax class when the type has none', function () {
    $default = TaxClass::factory()->create(['default' => true]);
    $productType = ProductType::factory()->create();

    $product = app(CreateProduct::class)->execute([
        'name' => ['en' => 'Espresso Cup'],
        'product_type_id' => $productType->id,
        'status' => 'draft',
    ]);

    expect($product->variants->first()->tax_class_id)->toBe($default->id);
});
