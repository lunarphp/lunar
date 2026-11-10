<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Lunar\Core\Facades\ShippingManifest;
use Lunar\Core\Models\CartAddress;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderAddress;
use Lunar\Core\Models\TaxClass;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Shipping\Models\ShippingRate;
use Lunar\Shipping\Models\ShippingZone;
use Lunar\Shipping\Observers\OrderObserver;
use Lunar\Shipping\Resolvers\ShippingZoneResolver;
use Lunar\Tests\Shipping\TestCase;
use Lunar\Tests\Shipping\TestUtils;

uses(TestCase::class)
    ->group('shipping', 'shipping-order');
uses(RefreshDatabase::class);
uses(TestUtils::class);

test('can store shipping zone against order', function () {

    Order::observe(OrderObserver::class);

    $currency = Currency::factory()->create([
        'default' => true,
    ]);

    $country = Country::factory()->create();

    TaxClass::factory()->create([
        'default' => true,
    ]);

    $shippingZone = ShippingZone::factory()->create([
        'type' => 'countries',
    ]);

    $shippingZone->countries()->attach($country);

    $shippingMethod = ShippingMethod::factory()->create([
        'driver' => 'ship-by',
        'data' => [
            'minimum_spend' => [
                "{$currency->code}" => 200,
            ],
        ],
    ]);

    $customerGroup = CustomerGroup::factory()->create([
        'default' => true,
    ]);
    $shippingMethod->customerGroups()->sync([
        $customerGroup->id => ['enabled' => true, 'visible' => true, 'starts_at' => now(), 'ends_at' => null],
    ]);

    $shippingRate = ShippingRate::factory()
        ->create([
            'shipping_method_id' => $shippingMethod->id,
            'shipping_zone_id' => $shippingZone->id,
        ]);

    $shippingRate->prices()->createMany([
        [
            'price' => 600,
            'min_quantity' => 1,
            'currency_id' => $currency->id,
        ],
        [
            'price' => 500,
            'min_quantity' => 700,
            'currency_id' => $currency->id,
        ],
        [
            'price' => 0,
            'min_quantity' => 800,
            'currency_id' => $currency->id,
        ],
    ]);

    $cart = $this->createCart($currency, 500);

    $cart->shippingAddress()->create(
        CartAddress::factory()->make([
            'country_id' => $country->id,
            'state' => null,
        ])->toArray()
    );

    $cart->billingAddress()->create(
        CartAddress::factory()->make([
            'country_id' => $country->id,
            'type' => 'billing',
            'state' => null,
        ])->toArray()
    );

    $shippingOption = ShippingManifest::getOptions($cart->refresh())->first();

    $cart->setShippingOption($shippingOption);

    $order = $cart->refresh()->createOrder();
    $orderShippingZone = $order->shippingZone->first();

    expect($orderShippingZone)->toBeInstanceOf(ShippingZone::class)
        ->and($orderShippingZone->id)
        ->toBe($shippingZone->id);
});

/**
 * The resolver runs an OR of EXISTS subqueries with no ORDER BY, so the
 * database decides which zone comes first. MySQL hands back the country
 * zone; SQLite happens to hand back the postcode zone and would hide the
 * bug. Pin the unfavourable order so the test means the same thing on both.
 */
function countryZonesFirst(): void
{
    app()->bind(ShippingZoneResolver::class, fn () => new class extends ShippingZoneResolver
    {
        public function get(): Collection
        {
            return parent::get()->sortBy(fn (ShippingZone $zone) => $zone->type === 'countries' ? 0 : 1)->values();
        }
    });
}

function orderShippingTo(Country $country, string $postcode): Order
{
    $order = Order::factory()->create();

    $order->shippingAddress()->create(
        OrderAddress::factory()->make([
            'country_id' => $country->id,
            'postcode' => $postcode,
            'type' => 'shipping',
            'state' => null,
        ])->toArray()
    );

    // fresh(): the created() hook already read shippingAddress as null and
    // Eloquent cached that; a real update runs on a freshly loaded order.
    return $order->fresh();
}

test('records the most specific zone the postcode resolved, not the first', function () {
    Order::observe(OrderObserver::class);
    countryZonesFirst();

    Currency::factory()->create(['default' => true]);
    $country = Country::factory()->create();

    $countryZone = ShippingZone::factory()->create(['type' => 'countries', 'name' => 'United Kingdom']);
    $countryZone->countries()->attach($country);

    $postcodeZone = ShippingZone::factory()->create(['type' => 'postcodes', 'name' => 'UK Mainland']);
    $postcodeZone->countries()->attach($country);
    $postcodeZone->postcodes()->create(['postcode' => 'CM9']);

    $order = orderShippingTo($country, 'CM9 4BQ');

    // Any change re-stamps the zone, the same path the checkout's updates take.
    $order->update(['customer_reference' => 'PO-1']);
    $order->refresh();

    expect($order->shippingZone->pluck('id')->all())->toBe([$postcodeZone->id])
        ->and($order->meta['shipping_zone'])->toBe('UK Mainland');
});

test('falls back to the country zone when no postcode zone matches', function () {
    Order::observe(OrderObserver::class);
    countryZonesFirst();

    Currency::factory()->create(['default' => true]);
    $country = Country::factory()->create();

    $countryZone = ShippingZone::factory()->create(['type' => 'countries', 'name' => 'United Kingdom']);
    $countryZone->countries()->attach($country);

    $postcodeZone = ShippingZone::factory()->create(['type' => 'postcodes', 'name' => 'UK Mainland']);
    $postcodeZone->countries()->attach($country);
    $postcodeZone->postcodes()->create(['postcode' => 'CM9']);

    $order = orderShippingTo($country, 'BT1 1AA');

    $order->update(['customer_reference' => 'PO-1']);
    $order->refresh();

    expect($order->shippingZone->pluck('id')->all())->toBe([$countryZone->id])
        ->and($order->meta['shipping_zone'])->toBe('United Kingdom');
});
