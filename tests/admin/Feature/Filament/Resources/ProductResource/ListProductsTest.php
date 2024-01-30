<?php

use Lunar\Admin\Filament\Resources\ProductResource;

uses(\Lunar\Tests\Admin\Feature\Filament\TestCase::class)
    ->group('resource.product');

it('can create product', function () {
    \Lunar\Models\Attribute::factory()->create([
        'type' => \Lunar\FieldTypes\TranslatedText::class,
        'attribute_type' => \Lunar\Models\Product::class,
        'handle' => 'name',
        'name' => [
            'en' => 'Name',
        ],
    ]);
    \Lunar\Models\TaxClass::factory()->create([
        'default' => true,
    ]);
    \Lunar\Models\Currency::factory()->create([
        'default' => true,
        'decimal_places' => 2,
    ]);
    $language = \Lunar\Models\Language::factory()->create([
        'default' => true,
    ]);

    $productType = \Lunar\Models\ProductType::factory()->create();

    $this->asStaff();

    \Livewire\Livewire::test(ProductResource\Pages\ListProducts::class)
        ->callAction('create', data: [
            'name' => [$language->code => 'Foo Bar'],
            'base_price' => 10.99,
            'sku' => 'ABCABCAB',
            'product_type_id' => $productType->id,
        ])->assertHasNoActionErrors();

    $this->assertDatabaseHas((new \Lunar\Models\Product())->getTable(), [
        'product_type_id' => $productType->id,
        'status' => 'draft',
        'attribute_data' => json_encode([
            'name' => [
                'field_type' => \Lunar\FieldTypes\TranslatedText::class,
                'value' => [
                    $language->code => 'Foo Bar',
                ],
            ],
        ]),
    ]);

    $this->assertDatabaseHas((new \Lunar\Models\ProductVariant())->getTable(), [
        'sku' => 'ABCABCAB',
    ]);

    $this->assertDatabaseHas((new \Lunar\Models\Price())->getTable(), [
        'price' => '1099',
    ]);
});
