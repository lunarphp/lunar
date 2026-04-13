<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Filament\Resources\ProductResource\Pages\ManageProductShipping;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.product');

it('can render product shipping page', function () {
    Language::factory()->create([
        'default' => true,
    ]);

    Currency::factory()->create([
        'default' => true,
    ]);

    $record = Product::factory()->create();

    ProductVariant::factory()->create([
        'product_id' => $record->id,
    ]);

    $this->asStaff(admin: true)
        ->get(ProductResource::getUrl('shipping', [
            'record' => $record,
        ]))
        ->assertSuccessful();
});

it('will show in navigation when only one variant exists', function () {
    Language::factory()->create([
        'default' => true,
    ]);

    Currency::factory()->create([
        'default' => true,
    ]);

    $record = Product::factory()->create();

    ProductVariant::factory()->create([
        'product_id' => $record->id,
    ]);

    $this->asStaff(admin: true)
        ->get(ProductResource::getUrl('edit', [
            'record' => $record,
        ]))
        ->assertSuccessful()
        ->assertSeeText(
            __('lunarpanel::product.pages.shipping.label')
        );
});

it('will not show in navigation when multiple variants exist', function () {
    Language::factory()->create([
        'default' => true,
    ]);

    Currency::factory()->create([
        'default' => true,
    ]);

    $record = Product::factory()->create();

    ProductVariant::factory(2)->create([
        'product_id' => $record->id,
    ]);

    $this->asStaff(admin: true)
        ->get(ProductResource::getUrl('edit', [
            'record' => $record,
        ]))
        ->assertSuccessful()
        ->assertDontSeeText(
            __('lunarpanel::product.pages.shipping.label')
        );
});

it('can update variant shipping', function () {
    $language = Language::factory()->create([
        'default' => true,
    ]);

    $currency = Currency::factory()->create([
        'default' => true,
        'decimal_places' => 2,
    ]);

    $record = Product::factory()->create();

    $variant = ProductVariant::factory()->create([
        'product_id' => $record->id,
    ]);

    $this->asStaff();

    Livewire::test(
        ManageProductShipping::class, [
            'record' => $record->getRouteKey(),
        ])->fillForm([
            'shippable' => true,
            'dimensions.length_value' => 100,
            'dimensions.length_unit' => 'cm',
            'dimensions.width_value' => 200,
            'dimensions.width_unit' => 'm',
            'dimensions.height_value' => 10,
            'dimensions.height_unit' => 'cm',
            'dimensions.weight_value' => 10,
            'dimensions.weight_unit' => 'g',
        ])->call('save')->assertHasNoErrors();

    $this->assertDatabaseHas((new ProductVariant)->getTable(), [
        'shippable' => true,
        'length_value' => 100,
        'length_unit' => 'cm',
        'width_value' => 200,
        'width_unit' => 'm',
        'height_value' => 10,
        'height_unit' => 'cm',
        'weight_value' => 10,
        'weight_unit' => 'g',
    ]);
});

it('can set shipping volume automatically', function () {
    $language = Language::factory()->create([
        'default' => true,
    ]);

    $currency = Currency::factory()->create([
        'default' => true,
        'decimal_places' => 2,
    ]);

    $record = Product::factory()->create();

    $variant = ProductVariant::factory()->create([
        'product_id' => $record->id,
    ]);

    $this->asStaff();

    Livewire::test(
        ManageProductShipping::class, [
            'record' => $record->getRouteKey(),
        ])->fillForm([
            'shippable' => true,
            'dimensions.length_value' => 100,
            'dimensions.length_unit' => 'cm',
            'dimensions.width_value' => 100,
            'dimensions.width_unit' => 'cm',
            'dimensions.height_value' => 100,
            'dimensions.height_unit' => 'cm',
            'dimensions.weight_value' => 10,
            'dimensions.weight_unit' => 'g',
        ])->call('save')->assertHasNoErrors();

    $this->assertDatabaseHas((new ProductVariant)->getTable(), [
        'volume_value' => 1000,
        'volume_unit' => 'l',
    ]);
});
