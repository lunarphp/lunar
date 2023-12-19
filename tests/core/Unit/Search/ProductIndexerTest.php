<?php

uses(\Lunar\Tests\Core\TestCase::class);
use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\Attribute;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Lunar\Search\ProductIndexer;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can return correct searchable data', function () {
    Language::factory()->create([
        'code' => 'en',
        'default' => true,
    ]);

    Language::factory()->create([
        'code' => 'dk',
        'default' => false,
    ]);

    $attributeA = Attribute::factory()->create([
        'attribute_type' => Product::class,
        'searchable' => true,
    ]);
    $attributeB = Attribute::factory()->create([
        'attribute_type' => Product::class,
        'searchable' => true,
    ]);
    $attributeC = Attribute::factory()->create([
        'attribute_type' => Product::class,
        'searchable' => false,
    ]);
    $attributeD = Attribute::factory()->create([
        'attribute_type' => Product::class,
        'type' => TranslatedText::class,
        'searchable' => true,
    ]);

    $product = Product::factory()->create([
        'attribute_data' => collect([
            $attributeA->handle => new Text('Attribute A'),
            $attributeB->handle => new Text('Attribute B'),
            $attributeC->handle => new Text('Attribute C'),
            $attributeD->handle => new TranslatedText([
                'en' => 'Attribute D EN',
                'dk' => 'Attribute D DK',
            ]),
        ]),
    ]);

    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
    ]);

    $data = app(ProductIndexer::class)->toSearchableArray($product);
    expect($data)->toHaveKey('id');
    expect($data['skus'])->toBe([$variant->sku]);
    expect($data['status'])->toEqual($product->status);
    expect($data['product_type'])->toEqual($product->productType->name);
    expect($data['brand'])->toEqual($product->brand?->name);
    expect($data)->toHaveKey($attributeA->handle);
    expect($data)->toHaveKey($attributeB->handle);
    $this->assertArrayNotHasKey($attributeC->handle, $data);
    expect($data)->toHaveKey($attributeD->handle.'_en');
    expect($data)->toHaveKey($attributeD->handle.'_dk');
});
