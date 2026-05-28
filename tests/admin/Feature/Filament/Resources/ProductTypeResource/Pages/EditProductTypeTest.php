<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\ProductTypeResource\Pages\EditProductType;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\ProductType;
use Lunar\Tests\Admin\TestCase;

uses(TestCase::class)
    ->group('resource.productType');

it('can associate attributes', function () {
    $productType = ProductType::factory()->create();

    $attributeA = Attribute::factory()->modelType('product')->create();
    $attributeB = Attribute::factory()->modelType('product')->create();

    $component = Livewire::actingAs($this->makeStaff(admin: true), 'staff')->test(EditProductType::class, [
        'record' => $productType->getRouteKey(),
    ])->fillForm([
        'mappedAttributes' => [$attributeA->id, $attributeB->id],
    ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas((new ProductType)->mappedAttributes()->getTable(), [
        'product_type_id' => $component->get('record')->id,
        'attribute_id' => $attributeA->id,
    ]);

    $this->assertDatabaseHas((new ProductType)->mappedAttributes()->getTable(), [
        'product_type_id' => $component->get('record')->id,
        'attribute_id' => $attributeB->id,
    ]);

    $component = Livewire::actingAs($this->makeStaff(admin: true), 'staff')->test(EditProductType::class, [
        'record' => $productType->getRouteKey(),
    ])->set('data.mappedAttributes', [$attributeA->id])->assertFormSet([
        'mappedAttributes' => [$attributeA->id],
    ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas((new ProductType)->mappedAttributes()->getTable(), [
        'product_type_id' => $component->get('record')->id,
        'attribute_id' => $attributeA->id,
    ]);

    $this->assertDatabaseMissing((new ProductType)->mappedAttributes()->getTable(), [
        'product_type_id' => $component->get('record')->id,
        'attribute_id' => $attributeB->id,
    ]);
});
