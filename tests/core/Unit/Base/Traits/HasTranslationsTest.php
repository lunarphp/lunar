<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Enums\FieldTypeEnum;
use Lunar\Core\FieldTypes\Dropdown;
use Lunar\Core\FieldTypes\ListField;
use Lunar\Core\FieldTypes\Text;
use Lunar\Core\FieldTypes\TranslatedText;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductOption;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

/**
 * Seed the attribute definitions a product's attribute_data refers to, so the
 * cast can resolve each handle to a stable attribute id.
 *
 * @param  array<string, FieldTypeEnum>  $handles
 */
function seedAttributes(array $handles): void
{
    foreach ($handles as $handle => $type) {
        Attribute::factory()->create([
            'handle' => $handle,
            'type' => $type->value,
        ]);
    }
}

test('can translate attribute data', function () {
    seedAttributes([
        'name' => FieldTypeEnum::TranslatedText,
        'description' => FieldTypeEnum::TranslatedText,
    ]);

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

test('can translate a translatable column', function () {
    $productOption = ProductOption::factory()->create([
        'name' => [
            'en' => 'English Option',
            'fr' => 'French Option',
        ],
    ]);

    expect($productOption->translate('name', 'en'))->toEqual('English Option');
    expect($productOption->translate('name', 'fr'))->toEqual('French Option');
});

test('can fallback when translation not present', function () {
    seedAttributes(['name' => FieldTypeEnum::TranslatedText]);

    $product = Product::factory()->create([
        'attribute_data' => [
            'name' => new TranslatedText(collect([
                'en' => new Text('English Name'),
                'fr' => new Text('French Name'),
            ])),
        ],
    ]);

    expect($product->translateAttribute('name', 'dk'))->toEqual('English Name');
});

test('can fallback to existing translation when current is missing', function () {
    seedAttributes(['name' => FieldTypeEnum::TranslatedText]);

    $product = Product::factory()->create([
        'attribute_data' => [
            'name' => new TranslatedText(collect([
                'en' => new Text('English Name'),
                'fr' => new Text(''),
            ])),
        ],
    ]);

    expect($product->attr('name', 'fr'))->toEqual('English Name');
});

test('can handle null values', function () {
    seedAttributes([
        'name' => FieldTypeEnum::TranslatedText,
        'description' => FieldTypeEnum::TranslatedText,
    ]);

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
    seedAttributes(['name' => FieldTypeEnum::TranslatedText]);

    $product = Product::factory()->create([
        'attribute_data' => [
            'name' => new TranslatedText(collect([
                'en' => new Text('English Name'),
                'fr' => new Text('French Name'),
            ])),
        ],
    ]);

    app()->setLocale('fr');

    expect($product->translateAttribute('name'))->toEqual('French Name');

    app()->setLocale('en');

    expect($product->translateAttribute('name'))->toEqual('English Name');
});

test('will fallback to first translation if nothing exists', function () {
    seedAttributes(['name' => FieldTypeEnum::TranslatedText]);

    $product = Product::factory()->create([
        'attribute_data' => [
            'name' => new TranslatedText(collect([
                'en' => new Text('English Name'),
                'fr' => new Text('French Name'),
            ])),
        ],
    ]);

    app()->setLocale('dk');

    expect($product->translateAttribute('name'))->toEqual('English Name');
});

test('will use fieldtype value if it doesnt have translations', function () {
    seedAttributes(['name' => FieldTypeEnum::Text]);

    $product = Product::factory()->create([
        'attribute_data' => [
            'name' => new Text('English Name'),
        ],
    ]);

    expect($product->translateAttribute('name'))->toEqual('English Name');
});

test('will return null if attribute doesnt exist', function () {
    seedAttributes(['name' => FieldTypeEnum::Text]);

    $product = Product::factory()->create([
        'attribute_data' => [
            'name' => new Text('English Name'),
        ],
    ]);

    expect($product->translateAttribute('foobar'))->toBeNull();
});

test('will return null if attribute value is null', function () {
    seedAttributes([
        'name' => FieldTypeEnum::Text,
        'description' => FieldTypeEnum::Text,
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
    seedAttributes([
        'name' => FieldTypeEnum::Text,
        'list' => FieldTypeEnum::ListField,
        'dropdown' => FieldTypeEnum::Dropdown,
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
});

test('can use shorthand function to translate attributes', function () {
    seedAttributes([
        'name' => FieldTypeEnum::TranslatedText,
        'description' => FieldTypeEnum::TranslatedText,
    ]);

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
