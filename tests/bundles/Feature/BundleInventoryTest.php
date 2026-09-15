<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Bundles\Models\Bundle;
use Lunar\Bundles\Models\BundleComponent;
use Lunar\Bundles\Models\BundleGroup;
use Lunar\Core\Contracts\Actions\Products\ResolvesInventory;
use Lunar\Core\Enums\SellingPolicy;
use Lunar\Core\Exceptions\Carts\CartException;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Validation\CartLine\CartLineStock;
use Lunar\Tests\Bundles\Support\Catalogue;
use Lunar\Tests\Bundles\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    $this->currency = Catalogue::store();
});

function stockedPart(int $stock, int $quantity = 1, ?BundleGroup $group = null, array $attributes = []): BundleComponent
{
    $variant = ProductVariant::factory()->inStock($stock)->create(array_merge([
        'selling_policy' => SellingPolicy::InStock,
    ], $attributes));

    return BundleComponent::factory()->make([
        'product_variant_id' => $variant->id,
        'bundle_group_id' => $group?->id,
        'quantity' => $quantity,
    ]);
}

test('a fixed bundle is available as the smallest number of complete units its components allow', function () {
    $bundle = Bundle::factory()->create();

    $bundle->components()->save(stockedPart(10, 1));
    $bundle->components()->save(stockedPart(9, 3));

    $variant = ProductVariant::query()->find($bundle->product_variant_id);

    expect($variant->getTotalInventory())->toBe(3)
        ->and($variant->canBeFulfilledAtQuantity(3))->toBeTrue()
        ->and($variant->canBeFulfilledAtQuantity(4))->toBeFalse();
});

test('a configurable bundle counts the best-stocked option of each group', function () {
    $bundle = Bundle::factory()->create();
    $group = BundleGroup::factory()->create(['bundle_id' => $bundle->id]);

    $bundle->components()->save(stockedPart(20, 1));
    $bundle->components()->save(stockedPart(2, 1, $group));
    $bundle->components()->save(stockedPart(7, 1, $group));

    expect(ProductVariant::query()->find($bundle->product_variant_id)->getTotalInventory())->toBe(7);
});

test('unlimited selling propagates only when every candidate component is unlimited', function () {
    $bundle = Bundle::factory()->create();
    $bundle->components()->save(stockedPart(0, 1, null, ['selling_policy' => SellingPolicy::Always]));

    $variant = ProductVariant::query()->find($bundle->product_variant_id);

    expect(app(ResolvesInventory::class)->execute($variant)->unlimited)->toBeTrue()
        ->and($variant->canBeFulfilledAtQuantity(500))->toBeTrue();

    $bundle->components()->save(stockedPart(4, 1));

    expect(app(ResolvesInventory::class)->execute($variant->fresh())->unlimited)->toBeFalse()
        ->and($variant->fresh()->canBeFulfilledAtQuantity(5))->toBeFalse();
});

test('a bundle with no components has nothing to sell', function () {
    $bundle = Bundle::factory()->create();

    $inventory = app(ResolvesInventory::class)->execute(ProductVariant::query()->find($bundle->product_variant_id));

    expect($inventory->available)->toBe(0)->and($inventory->unlimited)->toBeFalse();
});

test('an ordinary variant falls through to the core resolver', function () {
    $variant = ProductVariant::factory()->inStock(6)->create(['selling_policy' => SellingPolicy::InStock]);

    expect(ProductVariant::query()->find($variant->id)->getTotalInventory())->toBe(6);
});

test('the bundle variant\'s own stock is ignored', function () {
    $bundle = Bundle::factory()->create([
        'product_variant_id' => ProductVariant::factory()->inStock(100)->create(['selling_policy' => SellingPolicy::InStock])->id,
    ]);
    $bundle->components()->save(stockedPart(1, 1));

    expect(ProductVariant::query()->find($bundle->product_variant_id)->getTotalInventory())->toBe(1);
});

test('the core stock validator rejects a bundle beyond its derived stock', function () {
    $bundle = Bundle::factory()->create();
    $bundle->components()->save(stockedPart(4, 2));
    $variant = ProductVariant::query()->find($bundle->product_variant_id);
    $cart = Cart::factory()->create(['currency_id' => $this->currency->id]);

    expect(app(CartLineStock::class)->using(cart: $cart, purchasable: $variant, quantity: 2, meta: [])->validate())->toBeTrue();

    app(CartLineStock::class)->using(cart: $cart, purchasable: $variant, quantity: 3, meta: [])->validate();
})->throws(CartException::class);
