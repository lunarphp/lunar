<?php

uses(\Lunar\Tests\Core\TestCase::class)->group('translations');

use Lunar\FieldTypes\Dropdown;
use Lunar\FieldTypes\ListField;
use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\AttributeGroup;
use Lunar\Models\Product;
use Lunar\Models\ProductOption;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can translate attributes', function () {
    $attributeGroup = AttributeGroup::factory()->create([
        'name' => [
            'en' => 'English',
            'fr' => 'French',
        ],
    ]);

    $productOption = ProductOption::factory()->create([
        'name' => [
            'en' => 'English Option',
            'fr' => 'French Option',
        ],
    ]);

    expect($attributeGroup->translate('name', 'en'))->toEqual('English');
    expect($attributeGroup->translate('name', 'fr'))->toEqual('French');

    expect($productOption->translate('name', 'en'))->toEqual('English Option');
    expect($productOption->translate('name', 'fr'))->toEqual('French Option');

    $product = Product::factory()->create([
        'attribute_data' => [
            'name' => new TranslatedText(collect([
                'en' => new Text('English Name'),
                'fr' => new Text('French Name'),
            ])),
            'description' => new TranslatedText(collect([
                'en' => new Text('English Description'),
                'fr' => new Text('French Description'),
            ])),
        ],
    ]);

    expect($product->translateAttribute('name'))->toEqual('English Name');
    expect($product->translateAttribute('name', 'fr'))->toEqual('French Name');

    expect($product->translateAttribute('description'))->toEqual('English Description');
    expect($product->translateAttribute('description', 'fr'))->toEqual('French Description');
});

test('can fallback when translation not present', function () {
    $attributeGroup = AttributeGroup::factory()->create([
        'name' => [
            'en' => 'English',
            'fr' => 'French',
        ],
    ]);

    expect($attributeGroup->translate('name', 'dk'))->toEqual('English');

    $product = Product::factory()->create([
        'attribute_data' => [
            'name' => new TranslatedText(collect([
                'en' => new Text('English Name'),
                'fr' => new Text('French Name'),
            ])),
            'description' => new TranslatedText(collect([
                'en' => new Text('English Description'),
                'fr' => new Text('French Description'),
            ])),
        ],
    ]);

    expect($product->translateAttribute('name', 'dk'))->toEqual('English Name');
});

test('can handle null values', function () {
    $attributeGroup = AttributeGroup::factory()->create([
        'name' => [
            'en' => 'English',
            'fr' => 'French',
        ],
    ]);

    expect($attributeGroup->translate('name', 'dk'))->toEqual('English');

    $product = Product::factory()->create([
        'attribute_data' => [
            'name' => new TranslatedText(collect([
                'en' => null,
            ])),
            'description' => new TranslatedText(collect([
                'en' => null,
            ])),
        ],
    ]);

    expect($product->translateAttribute('name'))->toBeNull();
    expect($product->translateAttribute('description'))->toBeNull();
});

test('will translate based on locale by default', function () {
    $attributeGroup = AttributeGroup::factory()->create([
        'name' => [
            'en' => 'English',
            'fr' => 'French',
        ],
    ]);

    $productOption = ProductOption::factory()->create([
        'name' => [
            'en' => 'English Option',
            'fr' => 'French Option',
        ],
    ]);

    $product = Product::factory()->create([
        'attribute_data' => [
            'name' => new TranslatedText(collect([
                'en' => new Text('English Name'),
                'fr' => new Text('French Name'),
            ])),
        ],
    ]);

    app()->setLocale('fr');

    expect($attributeGroup->translate('name'))->toEqual('French');
    expect($product->translateAttribute('name'))->toEqual('French Name');
    expect($productOption->translate('name'))->toEqual('French Option');

    app()->setLocale('en');

    expect($attributeGroup->translate('name'))->toEqual('English');
    expect($product->translateAttribute('name'))->toEqual('English Name');
    expect($productOption->translate('name'))->toEqual('English Option');
});

test('will fallback to first translation if nothing exists', function () {
    $attributeGroup = AttributeGroup::factory()->create([
        'name' => [
            'en' => 'English',
            'fr' => 'French',
        ],
    ]);

    $productOption = ProductOption::factory()->create([
        'name' => [
            'en' => 'English Option',
            'fr' => 'French Option',
        ],
    ]);

    $product = Product::factory()->create([
        'attribute_data' => [
            'name' => new TranslatedText(collect([
                'en' => new Text('English Name'),
                'fr' => new Text('French Name'),
            ])),
        ],
    ]);

    app()->setLocale('dk');

    expect($attributeGroup->translate('name'))->toEqual('English');
    expect($productOption->translate('name'))->toEqual('English Option');
    expect($product->translateAttribute('name'))->toEqual('English Name');
});

test('will use fieldtype value if it doesnt have translations', function () {
    $attributeGroup = AttributeGroup::factory()->create([
        'name' => [
            'en' => 'English',
            'fr' => 'French',
        ],
        'handle' => 'some-handle',
    ]);

    $product = Product::factory()->create([
        'attribute_data' => [
            'name' => new Text('English Name'),
        ],
    ]);

    expect($attributeGroup->translate('handle'))->toEqual('some-handle');
    expect($product->translateAttribute('name'))->toEqual('English Name');
});

test('will return null if attribute doesnt exist', function () {
    $attributeGroup = AttributeGroup::factory()->create([
        'name' => [
            'en' => 'English',
            'fr' => 'French',
        ],
    ]);

    $product = Product::factory()->create([
        'attribute_data' => [
            'name' => new Text('English Name'),
        ],
    ]);

    expect($attributeGroup->translate('foobar'))->toBeNull();
    expect($product->translateAttribute('foobar'))->toBeNull();
});

test('will return null if attribute value is null', function () {
    AttributeGroup::factory()->create([
        'name' => [
            'en' => 'English',
            'fr' => 'French',
        ],
    ]);

    $product = Product::factory()->create([
        'attribute_data' => [
            'name' => new Text('English Name'),
            'description' => new Text(null),
        ],
    ]);

    expect($product->translateAttribute('description'))->toBeNull();
});

test('handle if we try and translate a non translatable attribute', function () {
    AttributeGroup::factory()->create([
        'name' => [
            'en' => 'English',
            'fr' => 'French',
        ],
    ]);

    $product = Product::factory()->create([
        'attribute_data' => [
            'name' => new Text('Test Name'),
            'list' => new ListField([
                'One',
                'Two',
                'Three',
            ]),
            'dropdown' => new Dropdown('Foobar'),
        ],
    ]);

    expect($product->translateAttribute('name'))->toEqual('Test Name');
    expect($product->translateAttribute('dropdown'))->toEqual('Foobar');
    expect($product->translateAttribute('list'))->toEqual(['One', 'Two', 'Three']);
})->group('a');

test('can use shorthand function to translate attributes', function () {
    $attributeGroup = AttributeGroup::factory()->create([
        'name' => [
            'en' => 'English',
            'fr' => 'French',
        ],
    ]);

    $productOption = ProductOption::factory()->create([
        'name' => [
            'en' => 'English Option',
            'fr' => 'French Option',
        ],
    ]);

    expect($attributeGroup->translate('name', 'en'))->toEqual('English');
    expect($attributeGroup->translate('name', 'fr'))->toEqual('French');

    expect($productOption->translate('name', 'en'))->toEqual('English Option');
    expect($productOption->translate('name', 'fr'))->toEqual('French Option');

    $product = Product::factory()->create([
        'attribute_data' => [
            'name' => new TranslatedText(collect([
                'en' => new Text('English Name'),
                'fr' => new Text('French Name'),
            ])),
            'description' => new TranslatedText(collect([
                'en' => new Text('English Description'),
                'fr' => new Text('French Description'),
            ])),
        ],
    ]);

    expect($product->attr('name'))->toEqual('English Name');
    expect($product->attr('name', 'fr'))->toEqual('French Name');

    expect($product->attr('description'))->toEqual('English Description');
    expect($product->attr('description', 'fr'))->toEqual('French Description');
});
