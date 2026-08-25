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

it('warns when the product is hidden from guests but visible to other groups', function () {
    Language::factory()->create(['default' => true]);

    $default = CustomerGroup::factory()->create([
        'default' => true,
    ]);

    $wholesale = CustomerGroup::factory()->create([
        'default' => false,
    ]);

    $product = Product::factory()->create(['status' => 'published']);

    $product->customerGroups()->updateExistingPivot($default->id, [
        'enabled' => false,
        'visible' => false,
    ]);

    $product->customerGroups()->updateExistingPivot($wholesale->id, [
        'enabled' => true,
        'visible' => true,
    ]);

    $this->asStaff(admin: true);

    Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
        ->assertSuccessful()
        ->assertSee(__('lunarpanel::product.status.availability.hidden_from_guests'));
});

it('does not warn when the default group is enabled for the product', function () {
    Language::factory()->create(['default' => true]);

    CustomerGroup::factory()->create([
        'default' => true,
    ]);

    $product = Product::factory()->create(['status' => 'published']);

    $this->asStaff(admin: true);

    Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
        ->assertSuccessful()
        ->assertDontSee(__('lunarpanel::product.status.availability.hidden_from_guests'));
});

it('warns when no default customer group exists', function () {
    Language::factory()->create(['default' => true]);

    $group = CustomerGroup::factory()->create([
        'default' => false,
    ]);

    $product = Product::factory()->create(['status' => 'published']);

    $product->customerGroups()->updateExistingPivot($group->id, [
        'enabled' => true,
        'visible' => true,
    ]);

    $this->asStaff(admin: true);

    Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
        ->assertSuccessful()
        ->assertSee(__('lunarpanel::product.status.availability.no_default_customer_group'));
});

it('suppresses availability warnings when product is in draft', function () {
    Language::factory()->create(['default' => true]);

    CustomerGroup::factory()->create([
        'default' => true,
    ]);

    $product = Product::factory()->create(['status' => 'draft']);

    $this->asStaff(admin: true);

    Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
        ->assertSuccessful()
        ->assertSee(__('lunarpanel::product.status.unpublished.content'))
        ->assertDontSee(__('lunarpanel::product.status.availability.customer_groups'))
        ->assertDontSee(__('lunarpanel::product.status.availability.channels'))
        ->assertDontSee(__('lunarpanel::product.status.availability.hidden_from_guests'))
        ->assertDontSee(__('lunarpanel::product.status.availability.no_default_customer_group'));
});

it('applies attribute default_value when product has no value for that attribute', function () {
    Language::factory()->create(['default' => true]);

    TaxClass::factory()->create(['default' => true]);

    $product = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $product->id]);

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
        'type' => Number::class,
        'default_value' => '99',
        'required' => false,
        'system' => false,
        'searchable' => false,
    ]);

    DB::table('lunar_attributables')->insert([
        'attribute_id' => $attribute->id,
        'attributable_type' => 'product_type',
        'attributable_id' => $product->productType->id,
    ]);

    // The product has no value for 'quantity' yet
    expect($product->attr('quantity'))->toBeNull();

    $this->asStaff(admin: true);

    // Saving without explicitly setting quantity should persist the default value
    Livewire::test(EditProduct::class, [
        'record' => $product->getRouteKey(),
        'pageClass' => 'productEdit',
    ])->call('save')->assertHasNoFormErrors();

    expect($product->refresh()->attr('quantity'))->toBe(99);
});

it('ignores a legacy default_value on field types that do not support one', function () {
    Language::factory()->create(['default' => true]);

    TaxClass::factory()->create(['default' => true]);

    $product = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $product->id]);

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
        'name' => ['en' => 'Is special'],
        'handle' => 'is_special',
        'type' => Toggle::class,
        'default_value' => 'true',
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

    Livewire::test(EditProduct::class, [
        'record' => $product->getRouteKey(),
        'pageClass' => 'productEdit',
    ])->call('save')->assertHasNoFormErrors();

    expect($product->refresh()->attr('is_special'))->toBeFalse();
});
