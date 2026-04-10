<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Filament\Resources\ProductResource\Pages\ManageProductIdentifiers;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.product');

it('can render product identifiers page', function () {
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
        ->get(ProductResource::getUrl('identifiers', [
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
            __('lunarpanel::product.pages.identifiers.label')
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
            __('lunarpanel::relationmanagers.pricing.title')
        );
});

it('can update variant identifiers', function () {
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
        ManageProductIdentifiers::class, [
            'record' => $record->getRouteKey(),
        ])->fillForm([
            'sku' => 'FOOBARSKU',
            'mpn' => 'FOOBARMPN',
            'gtin' => 'FOOBARGTIN',
            'ean' => 'FOOBAREAN',
        ])->call('save')->assertHasNoErrors();

    $this->assertDatabaseHas((new ProductVariant)->getTable(), [
        'sku' => 'FOOBARSKU',
        'mpn' => 'FOOBARMPN',
        'gtin' => 'FOOBARGTIN',
        'ean' => 'FOOBAREAN',
    ]);
});
