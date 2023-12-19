<?php

uses(\Lunar\Tests\Admin\Feature\Filament\TestCase::class)
    ->group('resource.product');

it('can render product prices create page', function () {
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
        ->get(\Lunar\Admin\Filament\Resources\ProductResource::getUrl('pricing', [
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
            __('lunarpanel::relationmanagers.pricing.title')
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
        ->get(\Lunar\Admin\Filament\Resources\ProductResource::getUrl('index', [
            'record' => $record,
        ]))
        ->assertSuccessful()
        ->assertDontSeeText(
            __('lunarpanel::relationmanagers.pricing.title')
        );
});

it('can create product price', function () {
    $language = \Lunar\Models\Language::factory()->create([
        'default' => true,
    ]);

    $currency = \Lunar\Models\Currency::factory()->create([
        'default' => true,
        'decimal_places' => 2,
    ]);

    $record = \Lunar\Models\Product::factory()->create();

    \Lunar\Models\ProductVariant::factory()->create([
        'product_id' => $record->id,
    ]);

    $this->asStaff();

    \Livewire\Livewire::test(
        \Lunar\Admin\Filament\Resources\ProductResource\Pages\ManageProductPricing::class, [
            'record' => $record->getRouteKey(),
        ])->assertFormSet([
            'price' => null,
        ])->callTableAction('create', data: [
            'price' => 10.99,
            'currency_id' => $currency->id,
            'tier' => 1,
        ])->assertHasNoErrors();

    $this->assertDatabaseHas((new \Lunar\Models\Price())->getTable(), [
        'price' => 1099,
        'tier' => 1,
        'currency_id' => $currency->id,
        'customer_group_id' => null,
    ]);
});
