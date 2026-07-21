<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\ProductTypes\DeleteProductType;
use Lunar\Core\Exceptions\ProductTypeActionException;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductType;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('deletes a product type without products', function () {
    $productType = ProductType::factory()->create();
    $attribute = Attribute::factory()->create();
    $productType->attributeMapping()->sync([$attribute->id]);

    app(DeleteProductType::class)->execute($productType);

    $this->assertDatabaseMissing('lunar_product_types', ['id' => $productType->id]);
    $this->assertDatabaseMissing('lunar_product_type_attribute', ['product_type_id' => $productType->id]);
});

test('refuses to delete a product type with products', function () {
    $product = Product::factory()->create();
    $productType = $product->productType;

    expect(DeleteProductType::isProtected($productType))->toBeTrue();

    expect(fn () => app(DeleteProductType::class)->execute($productType))
        ->toThrow(ProductTypeActionException::class);

    $this->assertDatabaseHas('lunar_product_types', ['id' => $productType->id]);
});
