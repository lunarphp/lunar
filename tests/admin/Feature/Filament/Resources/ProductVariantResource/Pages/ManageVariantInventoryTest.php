<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\ProductVariantResource\Pages\ManageVariantInventory;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.product-variant');

it('rejects a unit quantity below one', function () {
    Language::factory()->create(['default' => true]);
    Currency::factory()->create(['default' => true]);

    $product = Product::factory()->create();

    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
    ]);

    $this->asStaff();

    Livewire::test(
        ManageVariantInventory::class, [
            'record' => $variant->getRouteKey(),
        ])->fillForm([
            'unit_quantity' => 0,
        ])->call('save')
        ->assertHasFormErrors(['unit_quantity' => 'min']);
});
