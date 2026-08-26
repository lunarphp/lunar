<?php

use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Lunar\Admin\Events\ProductVariantInventoryUpdated;
use Lunar\Admin\Filament\Resources\ProductVariantResource\Pages\ManageVariantInventory;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.product-variant');

it('dispatches ProductVariantInventoryUpdated event when updating variant stock figures', function () {
    Event::fake([ProductVariantInventoryUpdated::class]);

    Language::factory()->create([
        'default' => true,
    ]);

    Currency::factory()->create([
        'default' => true,
        'decimal_places' => 2,
    ]);

    $product = Product::factory()->create();

    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
    ]);

    $this->asStaff();

    Livewire::test(
        ManageVariantInventory::class, [
            'record' => $variant->getRouteKey(),
        ])->fillForm([
            'stock' => 250,
            'backorder' => 25,
            'purchasable' => 'in_stock_or_on_backorder',
        ])->call('save')->assertHasNoErrors();

    Event::assertDispatched(ProductVariantInventoryUpdated::class, function ($event) use ($variant) {
        return $event->model->is($variant)
            && $event->model->stock == 250
            && $event->model->backorder == 25;
    });

    $this->assertDatabaseHas((new ProductVariant)->getTable(), [
        'id' => $variant->id,
        'stock' => 250,
        'backorder' => 25,
        'purchasable' => 'in_stock_or_on_backorder',
    ]);
});

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
