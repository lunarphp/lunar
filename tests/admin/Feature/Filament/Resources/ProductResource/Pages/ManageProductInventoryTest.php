<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Filament\Resources\ProductResource\Pages\ManageProductInventory;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
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

it('shows the default location it is managing', function () {
    Language::factory()->create([
        'default' => true,
    ]);

    Currency::factory()->create([
        'default' => true,
    ]);

    Location::factory()->create(['default' => true, 'name' => 'Warehouse A']);

    $record = Product::factory()->create();

    ProductVariant::factory()->create([
        'product_id' => $record->id,
    ]);

    $this->asStaff(admin: true)
        ->get(ProductResource::getUrl('inventory', [
            'record' => $record,
        ]))
        ->assertSuccessful()
        ->assertSee('Warehouse A');
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
        'backorder' => 99,
    ]);

    $trashedVariant->delete();

    $activeVariant = ProductVariant::factory()->create([
        'product_id' => $record->id,
        'backorder' => 42,
    ]);

    $this->asStaff();

    Livewire::test(
        ManageProductInventory::class, [
            'record' => $record->getRouteKey(),
        ])->assertSet('backorder', $activeVariant->backorder);
});

it('updates the variant selling policy', function () {
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
            'backorder' => 50,
            'purchasable' => 'in_stock_or_on_backorder',
        ])->call('save')->assertHasNoErrors();

    $this->assertDatabaseHas((new ProductVariant)->getTable(), [
        'id' => $variant->id,
        'backorder' => 50,
        'purchasable' => 'in_stock_or_on_backorder',
    ]);
});
