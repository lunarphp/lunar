<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\ProductTypeResource;
use Lunar\Admin\Filament\Resources\ProductTypeResource\Pages\CreateProductType;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\ProductType;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.productType');

it('can render product type create page', function () {
    $this->asStaff(admin: true)
        ->get(ProductTypeResource::getUrl('create'))
        ->assertSuccessful();
});

it('can create product type', function () {
    $productType = ProductType::factory()->make();

    $formData = [
        'name' => $productType->name,
    ];

    Livewire::actingAs($this->makeStaff(admin: true), 'staff')
        ->test(CreateProductType::class)
        ->fillForm($formData)
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(ProductType::class, $formData);
});

it('can associate attributes', function () {
    $productType = ProductType::factory()->make();

    $attributeA = Attribute::factory()->modelType('product')->create();
    $attributeB = Attribute::factory()->modelType('product')->create();

    $formData = [
        'name' => $productType->name,
    ];

    $component = Livewire::actingAs($this->makeStaff(admin: true), 'staff')
        ->test(CreateProductType::class)
        ->fillForm([
            ...$formData,
            'mappedAttributes' => [$attributeA->id, $attributeB->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas((new ProductType)->mappedAttributes()->getTable(), [
        'product_type_id' => $component->get('record')->id,
        'attribute_id' => $attributeA->id,
    ]);
});
