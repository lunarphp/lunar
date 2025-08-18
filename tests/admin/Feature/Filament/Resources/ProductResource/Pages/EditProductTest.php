<?php

use Livewire\Livewire;

uses(\Lunar\Tests\Admin\Unit\Filament\TestCase::class)
    ->group('resource.product');

it('can edit variant attributes', function ($attributeType, $attributeValue) {
    \Lunar\Models\CustomerGroup::factory()->create([
        'default' => true,
    ]);

    \Lunar\Models\Language::factory()->create([
        'default' => true,
    ]);

    $product = \Lunar\Models\Product::factory()->create();
    $variant = \Lunar\Models\ProductVariant::factory()->create([
        'product_id' => $product->id,
    ]);

    $group = \Lunar\Models\AttributeGroup::factory()->create([
        'attributable_type' => 'product_variant',
        'name' => [
            'en' => 'Variant Details',
        ],
        'handle' => 'variant_details',
        'position' => 1,
    ]);

    $attribute = \Lunar\Models\Attribute::factory()->create([
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

    \Illuminate\Support\Facades\DB::table('lunar_attributables')->insert([
        'attribute_id' => $attribute->id,
        'attributable_type' => 'product_type',
        'attributable_id' => $product->productType->id,
    ]);

    $this->asStaff(admin: true);

    $component = Livewire::test(\Lunar\Admin\Filament\Resources\ProductResource\Pages\EditProduct::class, [
        'record' => $product->id,
        'pageClass' => 'productEdit',
    ])->assertSuccessful();

    expect($variant->attr($attribute->handle))->toBeNull();

    $component->fillForm([
        'variant' => [
            $attribute->handle => new $attributeType($attributeValue),
        ],
    ])->call('save')
        ->assertHasNoFormErrors();

    expect($variant->refresh()->attr($attribute->handle))->toBe($attributeValue);
})->with([
    [\Lunar\FieldTypes\Text::class, 'Hello'],
    [\Lunar\FieldTypes\Toggle::class, true],
    [\Lunar\FieldTypes\Number::class, 100],
]);

it('can render all attribute data fields', function () {
    \Lunar\Admin\Support\Facades\AttributeData::registerFieldType(
        \Lunar\Tests\Admin\Stubs\FieldTypes\RepeaterField::class,
        \Lunar\Tests\Admin\Stubs\Support\FieldTypes\RepeaterField::class,
    );
    \Lunar\Models\Language::factory()->create([
        'default' => true,
    ]);

    \Lunar\Models\TaxClass::factory()->create([
        'default' => true,
    ]);

    // Ensure pricing-related defaults exist for variant rendering consistency
    \Lunar\Models\Currency::factory()->create([
        'default' => true,
    ]);

    $product = \Lunar\Models\Product::factory()->create();

    // Single variant so variant attribute form is visible
    \Lunar\Models\ProductVariant::factory()->create([
        'product_id' => $product->id,
    ]);

    $productGroup = \Lunar\Models\AttributeGroup::factory()->create([
        'attributable_type' => 'product',
        'name' => [
            'en' => 'Product Details',
        ],
        'handle' => 'product_details',
        'position' => 1,
    ]);

    $variantGroup = \Lunar\Models\AttributeGroup::factory()->create([
        'attributable_type' => 'product_variant',
        'name' => [
            'en' => 'Variant Details',
        ],
        'handle' => 'variant_details',
        'position' => 1,
    ]);

    $definitions = [
        ['handle' => 'text', 'type' => \Lunar\FieldTypes\Text::class, 'configuration' => []],
        ['handle' => 'richtext', 'type' => \Lunar\FieldTypes\Text::class, 'configuration' => ['richtext' => true]],
        ['handle' => 'dropdown', 'type' => \Lunar\FieldTypes\Dropdown::class, 'configuration' => ['lookups' => [
            ['label' => 'Red', 'value' => 'red'],
            ['label' => 'Blue', 'value' => 'blue'],
        ]]],
        ['handle' => 'list', 'type' => \Lunar\FieldTypes\ListField::class, 'configuration' => []],
        ['handle' => 'toggle', 'type' => \Lunar\FieldTypes\Toggle::class, 'configuration' => []],
        ['handle' => 'number', 'type' => \Lunar\FieldTypes\Number::class, 'configuration' => []],
        ['handle' => 'translated', 'type' => \Lunar\FieldTypes\TranslatedText::class, 'configuration' => []],
        ['handle' => 'youtube', 'type' => \Lunar\FieldTypes\YouTube::class, 'configuration' => []],
        ['handle' => 'vimeo', 'type' => \Lunar\FieldTypes\Vimeo::class, 'configuration' => []],
        ['handle' => 'file', 'type' => \Lunar\FieldTypes\File::class, 'configuration' => []],
        ['handle' => 'custom', 'type' => \Lunar\Tests\Admin\Stubs\FieldTypes\RepeaterField::class, 'configuration' => []],
    ];

    // Create attributes for the product and map them via product type
    foreach ($definitions as $idx => $def) {
        $attribute = \Lunar\Models\Attribute::factory()->create([
            'attribute_type' => 'product',
            'attribute_group_id' => $productGroup->id,
            'position' => $idx + 1,
            'name' => [
                'en' => ucfirst($def['handle']),
            ],
            'handle' => $def['handle'],
            'section' => 'main',
            'type' => $def['type'],
            'required' => false,
            'system' => false,
            'searchable' => false,
            'configuration' => $def['configuration'],
        ]);

        \Illuminate\Support\Facades\DB::table('lunar_attributables')->insert([
            'attribute_id' => $attribute->id,
            'attributable_type' => 'product_type',
            'attributable_id' => $product->productType->id,
        ]);
    }

    // Create attributes for the variant and map them via product type
    foreach ($definitions as $idx => $def) {
        $attribute = \Lunar\Models\Attribute::factory()->create([
            'attribute_type' => 'product_variant',
            'attribute_group_id' => $variantGroup->id,
            'position' => $idx + 1,
            'name' => [
                'en' => ucfirst($def['handle']),
            ],
            'handle' => $def['handle'],
            'section' => 'main',
            'type' => $def['type'],
            'required' => false,
            'system' => false,
            'searchable' => false,
            'configuration' => $def['configuration'],
        ]);

        \Illuminate\Support\Facades\DB::table('lunar_attributables')->insert([
            'attribute_id' => $attribute->id,
            'attributable_type' => 'product_type',
            'attributable_id' => $product->productType->id,
        ]);
    }

    $this->asStaff(admin: true);

    $component = Livewire::test(\Lunar\Admin\Filament\Resources\ProductResource\Pages\EditProduct::class, [
        'record' => $product->id,
        'pageClass' => 'productEdit',
    ])->assertSuccessful();

    // Assert product attribute_data fields render
    foreach ($definitions as $def) {
        $component->assertFormFieldExists('attribute_data.'.$def['handle']);
    }

    // Assert variant attribute fields render under the relationship
    foreach ($definitions as $def) {
        $component->assertFormFieldExists('variant.'.$def['handle']);
    }
});

it('can save attributes', function () {
    \Lunar\Models\Language::factory()->create([
        'default' => true,
    ]);

    \Lunar\Models\TaxClass::factory()->create([
        'default' => true,
    ]);

    $record = \Lunar\Models\Product::factory()->create();
    \Lunar\Models\ProductVariant::factory()->create([
        'product_id' => $record->id,
    ]);

    $group = \Lunar\Models\AttributeGroup::factory()->create([
        'attributable_type' => 'product',
        'name' => [
            'en' => 'Details',
        ],
        'handle' => 'details',
        'position' => 1,
    ]);

    $attribute = \Lunar\Models\Attribute::factory()->create([
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

    \Illuminate\Support\Facades\DB::table('lunar_attributables')->insert([
        'attribute_id' => $attribute->id,
        'attributable_type' => 'product_type',
        'attributable_id' => $record->productType->id,
    ]);

    $this->asStaff(admin: true);

    \Livewire\Livewire::test(\Lunar\Admin\Filament\Resources\ProductResource\Pages\EditProduct::class, [
        'record' => $record->getRouteKey(),
        'pageClass' => 'productEdit',
    ])->fillForm([
        'attribute_data' => [
            'name' => new \Lunar\FieldTypes\Text('New Product Name'),
        ],
    ])->call('save')->assertHasNoFormErrors();

    expect($record->refresh()->attr('name'))->toBe('New Product Name');
});

it('renders and saves the custom repeater attribute field', function () {
    \Lunar\Admin\Support\Facades\AttributeData::registerFieldType(
        \Lunar\Tests\Admin\Stubs\FieldTypes\RepeaterField::class,
        \Lunar\Tests\Admin\Stubs\Support\FieldTypes\RepeaterField::class,
    );
    \Lunar\Models\Language::factory()->create([
        'default' => true,
    ]);

    \Lunar\Models\TaxClass::factory()->create([
        'default' => true,
    ]);

    $product = \Lunar\Models\Product::factory()->create();
    $variant = \Lunar\Models\ProductVariant::factory()->create([
        'product_id' => $product->id,
    ]);

    $productGroup = \Lunar\Models\AttributeGroup::factory()->create([
        'attributable_type' => 'product',
        'name' => [
            'en' => 'Product Details',
        ],
        'handle' => 'product_details',
        'position' => 1,
    ]);

    $variantGroup = \Lunar\Models\AttributeGroup::factory()->create([
        'attributable_type' => 'product_variant',
        'name' => [
            'en' => 'Variant Details',
        ],
        'handle' => 'variant_details',
        'position' => 1,
    ]);

    $productAttribute = \Lunar\Models\Attribute::factory()->create([
        'attribute_type' => 'product',
        'attribute_group_id' => $productGroup->id,
        'position' => 1,
        'name' => [
            'en' => 'Custom',
        ],
        'handle' => 'custom',
        'section' => 'main',
        'type' => \Lunar\Tests\Admin\Stubs\FieldTypes\RepeaterField::class,
        'required' => false,
        'system' => false,
        'searchable' => false,
    ]);

    $variantAttribute = \Lunar\Models\Attribute::factory()->create([
        'attribute_type' => 'product_variant',
        'attribute_group_id' => $variantGroup->id,
        'position' => 1,
        'name' => [
            'en' => 'Custom',
        ],
        'handle' => 'custom',
        'section' => 'main',
        'type' => \Lunar\Tests\Admin\Stubs\FieldTypes\RepeaterField::class,
        'required' => false,
        'system' => false,
        'searchable' => false,
    ]);

    \Illuminate\Support\Facades\DB::table('lunar_attributables')->insert([
        'attribute_id' => $productAttribute->id,
        'attributable_type' => 'product_type',
        'attributable_id' => $product->productType->id,
    ]);

    \Illuminate\Support\Facades\DB::table('lunar_attributables')->insert([
        'attribute_id' => $variantAttribute->id,
        'attributable_type' => 'product_type',
        'attributable_id' => $product->productType->id,
    ]);

    $this->asStaff(admin: true);

    $component = Livewire::test(\Lunar\Admin\Filament\Resources\ProductResource\Pages\EditProduct::class, [
        'record' => $product->id,
        'pageClass' => 'productEdit',
    ])->assertSuccessful();

    $component->assertFormFieldExists('attribute_data.custom');
    $component->assertFormFieldExists('variant.custom');

    $component->fillForm([
        'attribute_data' => [
            'custom' => [
                ['label' => 'A', 'value' => '1'],
                ['label' => 'B', 'value' => '2'],
            ],
        ],
        'variant' => [
            'custom' => [
                ['label' => 'X', 'value' => '9'],
            ],
        ],
    ])->call('save')->assertHasNoFormErrors();

    $product = $product->refresh();
    $variant = $variant->refresh();

    expect($product->attr('custom'))->toBe([
        ['label' => 'A', 'value' => '1'],
        ['label' => 'B', 'value' => '2'],
    ]);

    expect($variant->attr('custom'))->toBe([
        ['label' => 'X', 'value' => '9'],
    ]);
});
