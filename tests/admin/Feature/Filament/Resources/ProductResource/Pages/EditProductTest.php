<?php

use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\ProductResource\Pages\EditProduct;
use Lunar\FieldTypes\Number;
use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\Toggle;
use Lunar\FieldTypes\TranslatedText as TranslatedTextField;
use Lunar\Models\Attribute;
use Lunar\Models\AttributeGroup;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\Tests\Admin\Unit\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.product');

it('can edit variant attributes', function ($attributeType, $attributeValue) {
    CustomerGroup::factory()->create([
        'default' => true,
    ]);

    Language::factory()->create([
        'default' => true,
    ]);

    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
    ]);

    $group = AttributeGroup::factory()->create([
        'attributable_type' => 'product_variant',
        'name' => [
            'en' => 'Variant Details',
        ],
        'handle' => 'variant_details',
        'position' => 1,
    ]);

    $attribute = Attribute::factory()->create([
        'attribute_type' => 'product_variant',
        'attribute_group_id' => $group->id,
        'position' => 1,
        'name' => [
            'en' => 'Test Attribute',
        ],
        'handle' => 'test-attribute',
        'section' => 'main',
        'type' => $attributeType,
        'required' => false,
        'system' => false,
        'searchable' => false,
    ]);

    DB::table('lunar_attributables')->insert([
        'attribute_id' => $attribute->id,
        'attributable_type' => 'product_type',
        'attributable_id' => $product->productType->id,
    ]);

    $this->asStaff(admin: true);

    $component = Livewire::test(EditProduct::class, [
        'record' => $product->id,
        'pageClass' => 'productEdit',
    ])->assertSuccessful();

    expect($variant->attr($attribute->handle))->toBeNull();

    $component->fillForm([
        'variant' => [
            $attribute->handle => $attributeValue,
        ],
    ])->call('save')
        ->assertHasNoFormErrors();

    expect($variant->refresh()->attr($attribute->handle))->toBe($attributeValue);
})->with([
    [Text::class, 'Hello'],
    [Toggle::class, true],
    [Number::class, 100],
]);

it('can load edit page with existing number attribute', function () {
    Language::factory()->create([
        'default' => true,
    ]);

    $product = Product::factory()->create();
    ProductVariant::factory()->create([
        'product_id' => $product->id,
    ]);

    $group = AttributeGroup::factory()->create([
        'attributable_type' => 'product',
        'name' => ['en' => 'Details'],
        'handle' => 'details',
        'position' => 1,
    ]);

    $attribute = Attribute::factory()->create([
        'attribute_type' => 'product',
        'attribute_group_id' => $group->id,
        'position' => 1,
        'name' => ['en' => 'Quantity'],
        'handle' => 'quantity',
        'section' => 'main',
        'type' => Number::class,
        'required' => false,
        'system' => false,
        'searchable' => false,
    ]);

    DB::table('lunar_attributables')->insert([
        'attribute_id' => $attribute->id,
        'attributable_type' => 'product_type',
        'attributable_id' => $product->productType->id,
    ]);

    $product->update([
        'attribute_data' => collect([
            'quantity' => new Number(123),
        ]),
    ]);

    $this->asStaff(admin: true);

    Livewire::test(EditProduct::class, [
        'record' => $product->getRouteKey(),
        'pageClass' => 'productEdit',
    ])->assertSuccessful();
});

it('can save attributes', function () {
    Language::factory()->create([
        'default' => true,
    ]);

    TaxClass::factory()->create([
        'default' => true,
    ]);

    $record = Product::factory()->create();
    ProductVariant::factory()->create([
        'product_id' => $record->id,
    ]);

    $group = AttributeGroup::factory()->create([
        'attributable_type' => 'product',
        'name' => [
            'en' => 'Details',
        ],
        'handle' => 'details',
        'position' => 1,
    ]);

    $attribute = Attribute::factory()->create([
        'attribute_type' => 'product',
        'attribute_group_id' => $group->id,
        'position' => 1,
        'name' => [
            'en' => 'Name',
        ],
        'handle' => 'name',
        'section' => 'main',
        'required' => false,
        'system' => false,
        'searchable' => false,
    ]);

    DB::table('lunar_attributables')->insert([
        'attribute_id' => $attribute->id,
        'attributable_type' => 'product_type',
        'attributable_id' => $record->productType->id,
    ]);

    $this->asStaff(admin: true);

    Livewire::test(EditProduct::class, [
        'record' => $record->getRouteKey(),
        'pageClass' => 'productEdit',
    ])->fillForm([
        'attribute_data' => [
            'name' => new Text('New Product Name'),
        ],
    ])->call('save')->assertHasNoFormErrors();

    expect($record->refresh()->attr('name'))->toBe('New Product Name');
});

it('hydrates translated rich text fields with all locale keys', function () {
    CustomerGroup::factory()->create([
        'default' => true,
    ]);

    Language::factory()->create([
        'code' => 'en',
        'default' => true,
    ]);

    Language::factory()->create([
        'code' => 'es',
        'default' => false,
    ]);

    TaxClass::factory()->create([
        'default' => true,
    ]);

    $product = Product::factory()->create([
        'attribute_data' => collect([
            'description' => new TranslatedTextField(collect()),
        ]),
    ]);

    ProductVariant::factory()->create([
        'product_id' => $product->id,
    ]);

    createTranslatedRichTextProductAttribute($product, 'description');

    $this->asStaff(admin: true);

    Livewire::test(EditProduct::class, [
        'record' => $product->getRouteKey(),
        'pageClass' => 'productEdit',
    ])
        ->assertSuccessful()
        ->assertSet('data.attribute_data.description.en', '')
        ->assertSet('data.attribute_data.description.es', '');
});

it('saves translated rich text fields from rich editor document payloads', function () {
    CustomerGroup::factory()->create([
        'default' => true,
    ]);

    Language::factory()->create([
        'code' => 'en',
        'default' => true,
    ]);

    Language::factory()->create([
        'code' => 'es',
        'default' => false,
    ]);

    TaxClass::factory()->create([
        'default' => true,
    ]);

    $product = Product::factory()->create([
        'attribute_data' => collect([
            'description' => new TranslatedTextField(collect()),
        ]),
    ]);

    ProductVariant::factory()->create([
        'product_id' => $product->id,
    ]);

    createTranslatedRichTextProductAttribute($product, 'description');

    $this->asStaff(admin: true);

    Livewire::test(EditProduct::class, [
        'record' => $product->getRouteKey(),
        'pageClass' => 'productEdit',
    ])
        ->fillForm([
            'attribute_data' => [
                'description' => [
                    'en' => [
                        'type' => 'doc',
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'content' => [
                                    [
                                        'type' => 'text',
                                        'text' => 'Rich description',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'es' => '',
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($product->refresh()->attr('description', 'en'))->toBe('<p>Rich description</p>');
});

function createTranslatedRichTextProductAttribute(Product $product, string $handle): void
{
    $group = AttributeGroup::factory()->create([
        'attributable_type' => 'product',
        'name' => [
            'en' => 'Details',
        ],
        'handle' => 'details',
        'position' => 1,
    ]);

    $attribute = Attribute::factory()->create([
        'attribute_type' => 'product',
        'attribute_group_id' => $group->id,
        'position' => 1,
        'name' => [
            'en' => 'Description',
        ],
        'handle' => $handle,
        'section' => 'main',
        'type' => TranslatedTextField::class,
        'required' => false,
        'system' => false,
        'searchable' => false,
        'configuration' => [
            'richtext' => true,
        ],
    ]);

    DB::table('lunar_attributables')->insert([
        'attribute_id' => $attribute->id,
        'attributable_type' => 'product_type',
        'attributable_id' => $product->productType->id,
    ]);
}
