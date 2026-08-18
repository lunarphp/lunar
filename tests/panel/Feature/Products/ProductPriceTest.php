<?php

use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Language::factory()->create(['default' => true, 'code' => 'en']);
    $this->currency = Currency::factory()->create(['default' => true, 'enabled' => true, 'decimal_places' => 2]);

    $this->product = Product::factory()->create();
    $this->variant = ProductVariant::factory()->create(['product_id' => $this->product->id]);
});

it('stores a base price row in minor units', function () {
    $this->post(route('panel.products.variants.prices.store', [$this->product, $this->variant]), [
        'currency_id' => $this->currency->id,
        'customer_group_id' => null,
        'min_quantity' => 1,
        'price' => 1099,
        'list_price' => 1299,
    ])->assertRedirect();

    $price = Price::sole();

    expect($price->price)->toBe(1099)
        ->and($price->list_price)->toBe(1299)
        ->and($price->priceable_id)->toBe($this->variant->id);
});

it('rejects a second row for the same currency, group and quantity', function () {
    Price::factory()->create([
        'priceable_type' => $this->variant->getMorphClass(),
        'priceable_id' => $this->variant->id,
        'currency_id' => $this->currency->id,
        'customer_group_id' => null,
        'min_quantity' => 1,
    ]);

    $this->post(route('panel.products.variants.prices.store', [$this->product, $this->variant]), [
        'currency_id' => $this->currency->id,
        'customer_group_id' => null,
        'min_quantity' => 1,
        'price' => 999,
    ])->assertSessionHasErrors('currency_id');
});

it('allows group and tier rows alongside the base row', function () {
    $group = CustomerGroup::factory()->create();

    $base = [
        'currency_id' => $this->currency->id,
        'price' => 1000,
        'list_price' => null,
    ];

    $this->post(route('panel.products.variants.prices.store', [$this->product, $this->variant]), [
        ...$base, 'customer_group_id' => null, 'min_quantity' => 1,
    ])->assertSessionHasNoErrors();

    $this->post(route('panel.products.variants.prices.store', [$this->product, $this->variant]), [
        ...$base, 'customer_group_id' => $group->id, 'min_quantity' => 1, 'price' => 900,
    ])->assertSessionHasNoErrors();

    $this->post(route('panel.products.variants.prices.store', [$this->product, $this->variant]), [
        ...$base, 'customer_group_id' => null, 'min_quantity' => 10, 'price' => 800,
    ])->assertSessionHasNoErrors();

    expect($this->variant->prices()->count())->toBe(3);
});

it('updates and deletes a price row', function () {
    $price = Price::factory()->create([
        'priceable_type' => $this->variant->getMorphClass(),
        'priceable_id' => $this->variant->id,
        'currency_id' => $this->currency->id,
        'customer_group_id' => null,
        'min_quantity' => 1,
        'price' => 1000,
    ]);

    $this->put(route('panel.products.variants.prices.update', [$this->product, $this->variant, $price]), [
        'currency_id' => $this->currency->id,
        'customer_group_id' => null,
        'min_quantity' => 1,
        'price' => 1150,
        'list_price' => null,
    ])->assertRedirect();

    expect($price->refresh()->price)->toBe(1150);

    $this->delete(route('panel.products.variants.prices.destroy', [$this->product, $this->variant, $price]))
        ->assertRedirect();

    expect(Price::count())->toBe(0);
});

it('scopes price routes to the owning product and variant', function () {
    $otherVariant = ProductVariant::factory()->create();
    $price = Price::factory()->create([
        'priceable_type' => $otherVariant->getMorphClass(),
        'priceable_id' => $otherVariant->id,
        'currency_id' => $this->currency->id,
        'min_quantity' => 1,
    ]);

    $this->delete(route('panel.products.variants.prices.destroy', [$this->product, $this->variant, $price]))
        ->assertNotFound();

    $this->post(route('panel.products.variants.prices.store', [$this->product, $otherVariant]), [
        'currency_id' => $this->currency->id,
        'customer_group_id' => null,
        'min_quantity' => 1,
        'price' => 100,
    ])->assertNotFound();
});
