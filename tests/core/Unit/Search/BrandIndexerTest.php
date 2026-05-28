<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Enums\FieldTypeEnum;
use Lunar\Core\FieldTypes\Text;
use Lunar\Core\FieldTypes\TranslatedText;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Language;
use Lunar\Core\Search\BrandIndexer;
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

    $attributeA = Attribute::factory()->modelType('brand')->create([
        'searchable' => true,
    ]);
    $attributeB = Attribute::factory()->modelType('brand')->create([
        'searchable' => true,
    ]);
    $attributeC = Attribute::factory()->modelType('brand')->create([
        'searchable' => false,
    ]);
    $attributeD = Attribute::factory()->modelType('brand')->create([
        'type' => FieldTypeEnum::TranslatedText->value,
        'searchable' => true,
    ]);

    $brand = Brand::factory()->create([
        'name' => 'Brand A',
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

    $data = app(BrandIndexer::class)->toSearchableArray($brand);

    expect($data['name'])->toEqual($brand->name)
        ->and($data)->toHaveKey('id')
        ->and($data)->toHaveKey($attributeA->handle)
        ->and($data)->toHaveKey($attributeB->handle)
        ->and($data)->not()->toHaveKey($attributeC->handle)
        ->and($data)->toHaveKey($attributeD->handle.'_en')
        ->and($data)->toHaveKey($attributeD->handle.'_dk');
});
