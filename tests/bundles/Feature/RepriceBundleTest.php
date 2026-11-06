<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Lunar\Bundles\Contracts\Actions\DefinesBundle;
use Lunar\Bundles\Enums\BundlePricing;
use Lunar\Bundles\Models\Bundle;
use Lunar\Bundles\Models\BundleComponent;
use Lunar\Bundles\Models\BundleGroup;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Bundles\Support\Catalogue;
use Lunar\Tests\Bundles\TestCase;

use function Pest\Laravel\artisan;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    $this->currency = Catalogue::store();
});

function bundlePrices(Bundle $bundle): Collection
{
    return Price::query()
        ->where('priceable_type', ProductVariant::morphName())
        ->where('priceable_id', $bundle->product_variant_id)
        ->orderBy('id')
        ->get();
}

test('a components bundle materialises one base row per currency every component is priced in', function () {
    $euro = Currency::factory()->create(['code' => 'EUR', 'decimal_places' => 2]);
    $body = Catalogue::variant($this->currency, 3000);
    $bag = Catalogue::variant($this->currency, 500);
    Price::factory()->create(['price' => 2800, 'list_price' => null, 'currency_id' => $euro->id, 'priceable_type' => $body->getMorphClass(), 'priceable_id' => $body->id]);

    $bundle = app(DefinesBundle::class)->execute(ProductVariant::factory()->create(), BundlePricing::Components);
    $bundle->syncComponents([
        ['variant' => $body, 'quantity' => 1],
        ['variant' => $bag, 'quantity' => 2],
    ]);

    $prices = bundlePrices($bundle);

    expect($prices)->toHaveCount(1)
        ->and($prices->first()->currency_id)->toBe($this->currency->id)
        ->and($prices->first()->price)->toBe(4000)
        ->and($prices->first()->list_price)->toBeNull()
        ->and($prices->first()->customer_group_id)->toBeNull();

    // The bag gains a euro price: the euro row appears.
    Price::factory()->create(['price' => 400, 'list_price' => null, 'currency_id' => $euro->id, 'priceable_type' => $bag->getMorphClass(), 'priceable_id' => $bag->id]);

    expect(bundlePrices($bundle)->firstWhere('currency_id', $euro->id)->price)->toBe(3600);
});

test('a discount percentage lowers the price and records the undiscounted sum as the list price', function () {
    $body = Catalogue::variant($this->currency, 3000);
    $bag = Catalogue::variant($this->currency, 1001);

    $bundle = app(DefinesBundle::class)->execute(ProductVariant::factory()->create(), BundlePricing::Components, 10);
    $bundle->syncComponents([
        ['variant' => $body, 'quantity' => 1],
        ['variant' => $bag, 'quantity' => 1],
    ]);

    $price = bundlePrices($bundle)->first();

    expect($price->price)->toBe(3601)
        ->and($price->list_price)->toBe(4001);
});

test('customer group rows are written where any component has a group price', function () {
    $trade = CustomerGroup::factory()->create(['handle' => 'trade']);
    $body = Catalogue::variant($this->currency, 3000);
    $bag = Catalogue::variant($this->currency, 500);
    Price::factory()->create(['price' => 2500, 'list_price' => null, 'currency_id' => $this->currency->id, 'customer_group_id' => $trade->id, 'priceable_type' => $body->getMorphClass(), 'priceable_id' => $body->id]);

    $bundle = app(DefinesBundle::class)->execute(ProductVariant::factory()->create(), BundlePricing::Components);
    $bundle->syncComponents([
        ['variant' => $body, 'quantity' => 1],
        ['variant' => $bag, 'quantity' => 1],
    ]);

    $prices = bundlePrices($bundle);

    expect($prices)->toHaveCount(2)
        ->and($prices->firstWhere('customer_group_id', null)->price)->toBe(3500)
        ->and($prices->firstWhere('customer_group_id', $trade->id)->price)->toBe(3000);
});

test('the default selection drives the price of a configurable bundle', function () {
    $body = Catalogue::variant($this->currency, 3000);
    $lensA = Catalogue::variant($this->currency, 1000);
    $lensB = Catalogue::variant($this->currency, 2000);

    $bundle = app(DefinesBundle::class)->execute(ProductVariant::factory()->create(), BundlePricing::Components);
    $bundle->syncGroups([['name' => ['en' => 'Lens'], 'min_selections' => 1, 'max_selections' => 1]]);
    $group = $bundle->groups->first();
    $bundle->syncComponents([
        ['variant' => $body, 'quantity' => 1],
        ['variant' => $lensA, 'quantity' => 1, 'group' => $group, 'default' => true],
        ['variant' => $lensB, 'quantity' => 1, 'group' => $group],
    ]);

    expect(bundlePrices($bundle)->first()->price)->toBe(4000);
});

test('stale rows go when a component loses its price, and price breaks are untouched', function () {
    $body = Catalogue::variant($this->currency, 3000);
    $bundle = app(DefinesBundle::class)->execute(ProductVariant::factory()->create(), BundlePricing::Components);
    $bundle->syncComponents([['variant' => $body, 'quantity' => 1]]);

    $break = Price::factory()->create(['price' => 2000, 'min_quantity' => 10, 'currency_id' => $this->currency->id, 'priceable_type' => ProductVariant::morphName(), 'priceable_id' => $bundle->product_variant_id]);

    expect(bundlePrices($bundle))->toHaveCount(2);

    $body->prices()->first()->delete();

    expect(bundlePrices($bundle)->pluck('id')->all())->toBe([$break->id]);
});

test('a fixed bundle is never repriced', function () {
    $body = Catalogue::variant($this->currency, 3000);
    $variant = Catalogue::variant($this->currency, 9999);

    $bundle = app(DefinesBundle::class)->execute($variant, BundlePricing::Fixed);
    $bundle->syncComponents([['variant' => $body, 'quantity' => 1]]);
    $body->prices()->first()->update(['price' => 1]);

    expect(bundlePrices($bundle)->first()->price)->toBe(9999);
});

test('a component price change reprices the bundles that include it', function () {
    $body = Catalogue::variant($this->currency, 3000);
    $bundle = app(DefinesBundle::class)->execute(ProductVariant::factory()->create(), BundlePricing::Components);
    $bundle->syncComponents([['variant' => $body, 'quantity' => 2]]);

    expect(bundlePrices($bundle)->first()->price)->toBe(6000);

    $body->prices()->first()->update(['price' => 3500]);

    expect(bundlePrices($bundle)->first()->price)->toBe(7000);
});

test('the reprice command recomputes every components bundle', function () {
    $body = Catalogue::variant($this->currency, 3000);
    $bundle = Bundle::factory()->componentPriced()->create();
    BundleComponent::factory()->create(['bundle_id' => $bundle->id, 'product_variant_id' => $body->id, 'quantity' => 1]);
    $fixed = Bundle::factory()->create();
    BundleComponent::factory()->create(['bundle_id' => $fixed->id, 'product_variant_id' => $body->id, 'quantity' => 1]);
    BundleGroup::factory()->create(['bundle_id' => $bundle->id, 'min_selections' => 0]);

    expect(bundlePrices($bundle))->toHaveCount(0);

    artisan('lunar:bundles:reprice')->assertSuccessful();

    expect(bundlePrices($bundle)->first()->price)->toBe(3000)
        ->and(bundlePrices($fixed))->toHaveCount(0);
});
