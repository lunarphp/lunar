<?php

use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true, 'enabled' => true, 'decimal_places' => 2]);
    Location::factory()->create(['default' => true]);

    $this->product = Product::factory()->create();
    $this->variants = ProductVariant::factory()->count(3)->create(['product_id' => $this->product->id]);
});

it('enables and disables a selection', function () {
    $ids = $this->variants->take(2)->pluck('id')->all();

    $this->post(route('panel.products.variants.bulk', $this->product), [
        'op' => 'disable',
        'ids' => $ids,
    ])->assertRedirect()->assertSessionHas('success');

    expect(ProductVariant::query()->whereIn('id', $ids)->pluck('enabled')->unique()->all())->toBe([false]);
});

it('sets the default-currency base price across a selection', function () {
    $ids = $this->variants->pluck('id')->all();

    $this->post(route('panel.products.variants.bulk', $this->product), [
        'op' => 'price',
        'ids' => $ids,
        'value' => 2599,
    ])->assertRedirect();

    foreach ($this->variants as $variant) {
        expect($variant->basePrices()->first()->price)->toBe(2599);
    }
});

it('sets stock at the default location through adjustments', function () {
    $variant = $this->variants->first();

    $this->post(route('panel.products.variants.bulk', $this->product), [
        'op' => 'stock',
        'ids' => [$variant->id],
        'value' => 12,
    ])->assertRedirect();

    expect($variant->refresh()->stock_on_hand)->toBe(12)
        ->and($variant->stockMovements()->where('type', 'adjustment')->count())->toBe(1);
});

it('deletes a selection but skips guarded rows', function () {
    [$deletable, $ordered, $survivor] = $this->variants;

    OrderLine::factory()->create([
        'purchasable_type' => $ordered->getMorphClass(),
        'purchasable_id' => $ordered->id,
    ]);

    $this->post(route('panel.products.variants.bulk', $this->product), [
        'op' => 'destroy',
        'ids' => [$deletable->id, $ordered->id],
    ])->assertRedirect()->assertSessionHas('error');

    $this->assertDatabaseMissing('lunar_product_variants', ['id' => $deletable->id]);
    $this->assertDatabaseHas('lunar_product_variants', ['id' => $ordered->id]);
});

it('validates the operation payload', function () {
    $this->post(route('panel.products.variants.bulk', $this->product), [
        'op' => 'explode',
        'ids' => [$this->variants->first()->id],
    ])->assertSessionHasErrors('op');

    $this->post(route('panel.products.variants.bulk', $this->product), [
        'op' => 'price',
        'ids' => [$this->variants->first()->id],
    ])->assertSessionHasErrors('value');
});
