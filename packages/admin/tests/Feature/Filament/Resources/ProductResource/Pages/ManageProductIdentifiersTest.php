<?php

uses(\Lunar\Admin\Tests\Feature\Filament\TestCase::class)
    ->group('resource.product');

it('can render product identifiers page', function () {
    \Lunar\Models\Language::factory()->create([
        'default' => true,
    ]);

    \Lunar\Models\Currency::factory()->create([
        'default' => true,
    ]);

    $record = \Lunar\Models\Product::factory()->create();

    \Lunar\Models\ProductVariant::factory()->create([
        'product_id' => $record->id,
    ]);

    $this->asStaff(admin: true)
        ->get(\Lunar\Admin\Filament\Resources\ProductResource::getUrl('identifiers', [
            'record' => $record,
        ]))
        ->assertSuccessful();
});

it('will show in navigation when only one variant exists', function () {
    \Lunar\Models\Language::factory()->create([
        'default' => true,
    ]);

    \Lunar\Models\Currency::factory()->create([
        'default' => true,
    ]);

    $record = \Lunar\Models\Product::factory()->create();

    \Lunar\Models\ProductVariant::factory()->create([
        'product_id' => $record->id,
    ]);

    $this->asStaff(admin: true)
        ->get(\Lunar\Admin\Filament\Resources\ProductResource::getUrl('edit', [
            'record' => $record,
        ]))
        ->assertSuccessful()
        ->assertSeeText(
            __('lunarpanel::product.pages.identifiers.label')
        );
});

it('will not show in navigation when multiple variants exist', function () {
    \Lunar\Models\Language::factory()->create([
        'default' => true,
    ]);

    \Lunar\Models\Currency::factory()->create([
        'default' => true,
    ]);

    $record = \Lunar\Models\Product::factory()->create();

    \Lunar\Models\ProductVariant::factory(2)->create([
        'product_id' => $record->id,
    ]);

    $this->asStaff(admin: true)
        ->get(\Lunar\Admin\Filament\Resources\ProductResource::getUrl('edit', [
            'record' => $record,
        ]))
        ->assertSuccessful()
        ->assertDontSeeText(
            __('lunarpanel::relationmanagers.pricing.title')
        );
});

it('can update variant identifiers', function () {
    $language = \Lunar\Models\Language::factory()->create([
        'default' => true,
    ]);

    $currency = \Lunar\Models\Currency::factory()->create([
        'default' => true,
        'decimal_places' => 2,
    ]);

    $record = \Lunar\Models\Product::factory()->create();

    $variant = \Lunar\Models\ProductVariant::factory()->create([
        'product_id' => $record->id,
    ]);

    $this->asStaff();

    \Livewire\Livewire::test(
        \Lunar\Admin\Filament\Resources\ProductResource\Pages\ManageProductIdentifiers::class, [
            'record' => $record->getRouteKey(),
        ])->fillForm([
            'sku' => 'FOOBARSKU',
            'mpn' => 'FOOBARMPN',
            'gtin' => 'FOOBARGTIN',
            'ean' => 'FOOBAREAN',
        ])->call('save')->assertHasNoErrors();

    $this->assertDatabaseHas((new \Lunar\Models\ProductVariant())->getTable(), [
        'sku' => 'FOOBARSKU',
        'mpn' => 'FOOBARMPN',
        'gtin' => 'FOOBARGTIN',
        'ean' => 'FOOBAREAN',
    ]);
});
