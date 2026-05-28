<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Lunar\Core\Enums\FieldTypeEnum;
use Lunar\Core\FieldTypes\Text;
use Lunar\Core\FieldTypes\TranslatedText;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Search\ScoutIndexer;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class)->group('search', 'indexer');

uses(RefreshDatabase::class);

test('can get correct index name', function () {
    Config::set('scout.prefix', 'lt_');

    $product = Product::factory()->create();
    $collection = Collection::factory()->create();

    $productIndex = app(ScoutIndexer::class)->searchableAs($product);
    $collectionIndex = app(ScoutIndexer::class)->searchableAs($collection);

    expect($productIndex)->toEqual('lt_products');
    expect($collectionIndex)->toEqual('lt_collections');
});

test('searchable is enabled by default', function () {
    $product = Product::factory()->create();
    $collection = Collection::factory()->create();

    expect(app(ScoutIndexer::class)->shouldBeSearchable($product))->toBeTrue();
    expect(app(ScoutIndexer::class)->shouldBeSearchable($collection))->toBeTrue();
});

test('can return searchable array', function () {
    $product = Product::factory()->create();

    $data = app(ScoutIndexer::class)->toSearchableArray($product);

    expect($data)->toBe([
        'id' => (string) $product->id,
    ]);
});

test('includes searchable attributes in searchable array', function () {
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

    $data = app(ScoutIndexer::class)->toSearchableArray($product);

    expect($data)->toHaveKey('id');
    expect($data)->toHaveKey($attributeA->handle);
    expect($data)->toHaveKey($attributeB->handle);
    $this->assertArrayNotHasKey($attributeC->handle, $data);
    expect($data)->toHaveKey($attributeD->handle.'_en');
    expect($data)->toHaveKey($attributeD->handle.'_dk');
});
