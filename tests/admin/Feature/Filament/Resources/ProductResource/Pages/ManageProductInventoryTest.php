<?php

use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Lunar\Admin\Events\ProductVariantInventoryUpdated;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Filament\Resources\ProductResource\Pages\ManageProductInventory;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.product');

it('can render product inventory page', function () {
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
        ->get(ProductResource::getUrl('inventory', [
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
            __('lunarpanel::product.pages.inventory.label')
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
            __('lunarpanel::product.pages.inventory.label')
        );
});

it('will show in navigation when only one active variant remains after trashing', function () {
    Language::factory()->create([
        'default' => true,
    ]);

    Currency::factory()->create([
        'default' => true,
    ]);

    $record = Product::factory()->create();

    $variants = ProductVariant::factory(2)->create([
        'product_id' => $record->id,
    ]);

    $variants->first()->delete();

    $this->asStaff(admin: true)
        ->get(ProductResource::getUrl('edit', [
            'record' => $record,
        ]))
        ->assertSuccessful()
        ->assertSeeText(
            __('lunarpanel::product.pages.inventory.label')
        );
});

it('mounts inventory page using the active variant when a trashed variant exists with a lower id', function () {
    Language::factory()->create([
        'default' => true,
    ]);

    Currency::factory()->create([
        'default' => true,
    ]);

    $record = Product::factory()->create();

    $trashedVariant = ProductVariant::factory()->create([
        'product_id' => $record->id,
        'stock' => 99,
    ]);

    $trashedVariant->delete();

    $activeVariant = ProductVariant::factory()->create([
        'product_id' => $record->id,
        'stock' => 42,
    ]);

    $this->asStaff();

    Livewire::test(
        ManageProductInventory::class, [
            'record' => $record->getRouteKey(),
        ])->assertSet('stock', $activeVariant->stock);
});

it('can update variant stock figures', function () {
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
        ManageProductInventory::class, [
            'record' => $record->getRouteKey(),
        ])->fillForm([
            'stock' => 500,
            'backorder' => 50,
            'purchasable' => 'in_stock_or_on_backorder',
        ])->call('save')->assertHasNoErrors();

    $this->assertDatabaseHas((new ProductVariant)->getTable(), [
        'stock' => 500,
        'backorder' => 50,
        'purchasable' => 'in_stock_or_on_backorder',
    ]);
});

it('dispatches ProductVariantInventoryUpdated event when updating variant stock figures', function () {
    Event::fake([ProductVariantInventoryUpdated::class]);

    Language::factory()->create([
        'default' => true,
    ]);

    Currency::factory()->create([
        'default' => true,
        'decimal_places' => 2,
    ]);

    $record = Product::factory()->create();

    $variant = ProductVariant::factory()->create([
        'product_id' => $record->id,
    ]);

    $this->asStaff();

    Livewire::test(
        ManageProductInventory::class, [
            'record' => $record->getRouteKey(),
        ])->fillForm([
            'stock' => 500,
            'backorder' => 50,
            'purchasable' => 'in_stock_or_on_backorder',
        ])->call('save')->assertHasNoErrors();

    Event::assertDispatched(ProductVariantInventoryUpdated::class, function ($event) use ($variant) {
        return $event->model->is($variant)
            && $event->model->stock == 500
            && $event->model->backorder == 50;
    });
});
