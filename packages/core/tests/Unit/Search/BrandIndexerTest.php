<?php

uses(\Lunar\Tests\TestCase::class);
use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\Attribute;
use Lunar\Models\Brand;
use Lunar\Models\Language;
use Lunar\Search\BrandIndexer;

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
        'attribute_type' => Brand::class,
        'searchable' => true,
    ]);
    $attributeB = Attribute::factory()->create([
        'attribute_type' => Brand::class,
        'searchable' => true,
    ]);
    $attributeC = Attribute::factory()->create([
        'attribute_type' => Brand::class,
        'searchable' => false,
    ]);
    $attributeD = Attribute::factory()->create([
        'attribute_type' => Brand::class,
        'type' => TranslatedText::class,
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

    expect($data['name'])->toEqual($brand->name);
    expect($data)->toHaveKey('id');
    expect($data)->toHaveKey($attributeA->handle);
    expect($data)->toHaveKey($attributeB->handle);
    $this->assertArrayNotHasKey($attributeC->handle, $data);
    expect($data)->toHaveKey($attributeD->handle.'_en');
    expect($data)->toHaveKey($attributeD->handle.'_dk');
});
