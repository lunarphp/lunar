<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Enums\FieldTypeEnum;
use Lunar\Core\FieldTypes\Text;
use Lunar\Core\FieldTypes\TranslatedText;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Search\ProductIndexer;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class)->group('search', 'indexer');

uses(RefreshDatabase::class);

test('can return correct searchable data', function () {
    Language::factory()->create([
        'code' => 'en',
        'default' => true,
    ]);

    Language::factory()->create([
        'code' => 'dk',
        'default' => false,
    ]);

    $attributeA = Attribute::factory()->modelType('product')->create([
        'searchable' => true,
    ]);
    $attributeB = Attribute::factory()->modelType('product')->create([
        'searchable' => true,
    ]);
    $attributeC = Attribute::factory()->modelType('product')->create([
        'searchable' => false,
    ]);
    $attributeD = Attribute::factory()->modelType('product')->create([
        'type' => FieldTypeEnum::TranslatedText->value,
        'searchable' => true,
    ]);

    $product = Product::factory()->create([
        'name' => collect([
            'en' => 'Trainers',
            'dk' => 'Løbesko',
        ]),
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
    expect($data['skus_normalised'])->toBe([strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', $variant->sku))]);
    expect($data['status'])->toEqual((string) $product->status);
    expect($data['product_type'])->toEqual($product->productType->name);
    expect($data['brand'])->toEqual($product->brand?->name);
    expect($data)->toHaveKey($attributeA->handle);
    expect($data)->toHaveKey($attributeB->handle);
    $this->assertArrayNotHasKey($attributeC->handle, $data);
    expect($data)->toHaveKey($attributeD->handle.'_en');
    expect($data)->toHaveKey($attributeD->handle.'_dk');

    // Dedicated translatable columns are indexed per locale.
    expect($data['name_en'])->toBe('Trainers');
    expect($data['name_dk'])->toBe('Løbesko');
});

test('normalised skus drop separators and uppercase the code', function () {
    Language::factory()->create(['code' => 'en', 'default' => true]);

    $product = Product::factory()->create();

    ProductVariant::factory()->create(['product_id' => $product->id, 'sku' => 'hag-mb-32a/bcu.1']);
    ProductVariant::factory()->create(['product_id' => $product->id, 'sku' => null]);

    $data = app(ProductIndexer::class)->toSearchableArray($product->fresh());

    expect($data['skus_normalised'])->toBe(['HAGMB32ABCU1'])
        ->and(app(ProductIndexer::class)->getExactMatchFields())->toBe(['skus', 'skus_normalised']);
});
