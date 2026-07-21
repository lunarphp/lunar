<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\ProductResource\Pages\EditProduct;
use Lunar\Core\Enums\FieldTypeEnum;
use Lunar\Core\FieldTypes\Number;
use Lunar\Core\FieldTypes\Text;
use Lunar\Core\FieldTypes\TranslatedText as TranslatedTextField;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\AttributeGroup;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\TaxClass;
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
        'name' => 'Variant Details',
        'handle' => 'variant_details',
        'position' => 1,
    ]);

    $attribute = Attribute::factory()->modelType('product_variant')->create([
        'attribute_group_id' => $group->id,
        'position' => 1,
        'name' => 'Test Attribute',
        'handle' => 'test_attribute',
        'type' => $attributeType,
        'required' => false,
        'system' => false,
        'searchable' => false,
    ]);

    $product->productType->attributeMapping()->attach($attribute->id);

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
    [FieldTypeEnum::Text->value, 'Hello'],
    [FieldTypeEnum::Toggle->value, true],
    [FieldTypeEnum::Number->value, 100],
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
        'name' => 'Details',
        'handle' => 'details',
        'position' => 1,
    ]);

    $attribute = Attribute::factory()->modelType('product')->create([
        'attribute_group_id' => $group->id,
        'position' => 1,
        'name' => 'Quantity',
        'handle' => 'quantity',
        'type' => FieldTypeEnum::Number->value,
        'required' => false,
        'system' => false,
        'searchable' => false,
    ]);

    $product->productType->attributeMapping()->attach($attribute->id);

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
        'name' => 'Details',
        'handle' => 'details',
        'position' => 1,
    ]);

    $attribute = Attribute::factory()->modelType('product')->create([
        'attribute_group_id' => $group->id,
        'position' => 1,
        'name' => 'Name',
        'handle' => 'name',
        'required' => false,
        'system' => false,
        'searchable' => false,
    ]);

    $record->productType->attributeMapping()->attach($attribute->id);

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

    $product = Product::factory()->create();

    ProductVariant::factory()->create([
        'product_id' => $product->id,
    ]);

    createTranslatedRichTextProductAttribute($product, 'description');

    $product->update([
        'attribute_data' => collect([
            'description' => new TranslatedTextField(collect()),
        ]),
    ]);

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

    $product = Product::factory()->create();

    ProductVariant::factory()->create([
        'product_id' => $product->id,
    ]);

    createTranslatedRichTextProductAttribute($product, 'description');

    $product->update([
        'attribute_data' => collect([
            'description' => new TranslatedTextField(collect()),
        ]),
    ]);

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
        'name' => 'Details',
        'handle' => 'details',
        'position' => 1,
    ]);

    $attribute = Attribute::factory()->modelType('product')->create([
        'attribute_group_id' => $group->id,
        'position' => 1,
        'name' => 'Description',
        'handle' => $handle,
        'type' => FieldTypeEnum::TranslatedText->value,
        'required' => false,
        'system' => false,
        'searchable' => false,
        'configuration' => [
            'richtext' => true,
        ],
    ]);

    $product->productType->attributeMapping()->attach($attribute->id);
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
        ->assertSee(__('lunar-filament::product.status.draft.content'))
        ->assertDontSee(__('lunar-filament::product.status.availability.customer_groups'))
        ->assertDontSee(__('lunar-filament::product.status.availability.channels'))
        ->assertDontSee(__('lunar-filament::product.status.availability.hidden_from_guests'))
        ->assertDontSee(__('lunar-filament::product.status.availability.no_default_customer_group'));
});
