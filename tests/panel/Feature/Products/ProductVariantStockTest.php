<?php

use Lunar\Core\Models\Language;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\Staff;
use Lunar\Panel\PanelManager;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Language::factory()->create(['default' => true, 'code' => 'en']);

    $this->product = Product::factory()->create();
    $this->variant = ProductVariant::factory()->inStock(10)->create(['product_id' => $this->product->id]);
    $this->location = Location::query()->where('default', true)->sole();
});

it('sets on hand through an adjustment movement and refreshes the rollup', function () {
    $this->post(route('panel.products.variants.stock.adjust', [$this->product, $this->variant]), [
        'location_id' => $this->location->id,
        'on_hand' => 25,
    ])->assertRedirect();

    $this->variant->refresh();

    expect($this->variant->stockLevels()->where('location_id', $this->location->id)->value('on_hand'))->toBe(25)
        ->and($this->variant->stock_on_hand)->toBe(25)
        ->and($this->variant->stockMovements()->where('type', 'adjustment')->count())->toBe(1);
});

it('writes no movement when the figure is unchanged', function () {
    $this->post(route('panel.products.variants.stock.adjust', [$this->product, $this->variant]), [
        'location_id' => $this->location->id,
        'on_hand' => 10,
    ])->assertRedirect();

    expect($this->variant->stockMovements()->where('type', 'adjustment')->count())->toBe(0);
});

it('scopes the stock route to the owning product', function () {
    $foreign = ProductVariant::factory()->create();

    $this->post(route('panel.products.variants.stock.adjust', [$this->product, $foreign]), [
        'location_id' => $this->location->id,
        'on_hand' => 5,
    ])->assertNotFound();
});

it('sets incoming stock without recording a movement', function () {
    $this->post(route('panel.products.variants.stock.incoming', [$this->product, $this->variant]), [
        'location_id' => $this->location->id,
        'incoming' => 7,
    ])->assertRedirect();

    $this->variant->refresh();

    expect($this->variant->stockLevels()->where('location_id', $this->location->id)->value('incoming'))->toBe(7)
        ->and($this->variant->stock_incoming)->toBe(7)
        ->and($this->variant->stock_on_hand)->toBe(10)
        ->and($this->variant->stockMovements()->count())->toBe(1);
});

it('rejects negative incoming stock', function () {
    $this->post(route('panel.products.variants.stock.incoming', [$this->product, $this->variant]), [
        'location_id' => $this->location->id,
        'incoming' => -1,
    ])->assertSessionHasErrors('incoming');
});

it('refuses incoming stock entry once it is turned off', function () {
    app(PanelManager::class)->withoutIncomingStockEntry();

    $this->post(route('panel.products.variants.stock.incoming', [$this->product, $this->variant]), [
        'location_id' => $this->location->id,
        'incoming' => 7,
    ])->assertForbidden();

    expect($this->variant->fresh()->stock_incoming)->toBe(0);

    $this->get(route('panel.products.variants.edit', [$this->product, $this->variant]))
        ->assertInertia(fn ($page) => $page->where('urls.stockIncoming', null));
});
