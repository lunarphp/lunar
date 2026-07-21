<?php

use Lunar\Core\Models\Language;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\Staff;
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
