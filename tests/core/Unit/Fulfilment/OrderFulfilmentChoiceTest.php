<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\DataTypes\ShippingOption;
use Lunar\Core\Drivers\FulfilmentMethods\Pickup;
use Lunar\Core\Drivers\FulfilmentMethods\Shipping;
use Lunar\Core\Facades\ShippingManifest;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartAddress;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\TaxClass;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    CustomerGroup::factory()->create(['default' => true]);
    TaxClass::factory()->create(['default' => true]);
    $this->currency = Currency::factory()->create(['default' => true, 'decimal_places' => 2]);
    $this->warehouse = Location::factory()->create(['default' => true]);
    $this->cart = Cart::factory()->create(['currency_id' => $this->currency->id]);
});

function orderVariant(Currency $currency, array $attributes = []): ProductVariant
{
    $variant = ProductVariant::factory()->inStock(100)->create($attributes);

    Price::factory()->create([
        'price' => 1000,
        'currency_id' => $currency->id,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
    ]);

    return $variant;
}

function readyForOrder(Cart $cart, ?Location $pickupLocation = null): Cart
{
    $option = new ShippingOption(
        name: 'Option',
        description: 'Option',
        identifier: $pickupLocation ? 'PICKUP' : 'DELIVERY',
        price: new PriceValue(500, $cart->currency),
        taxClass: TaxClass::getDefault(),
        pickupLocation: $pickupLocation,
        meta: ['zone' => 'UK'],
    );

    ShippingManifest::addOption($option);

    foreach (['billing', 'shipping'] as $type) {
        CartAddress::factory()->create([
            'type' => $type,
            'cart_id' => $cart->id,
            'shipping_option' => $type === 'shipping' ? $option->getIdentifier() : null,
        ]);
    }

    return $cart->refresh();
}

function place(Order $order): Order
{
    $order->update(['placed_at' => now()]);

    return $order->refresh();
}

test('a mixed cart stamps each line and splits fulfilments by method', function () {
    $variant = orderVariant($this->currency);

    $this->cart->add($variant, 1, fulfilmentMethod: Pickup::KEY);
    $this->cart->add($variant, 2);

    $order = place(readyForOrder($this->cart)->createOrder());

    expect($order->productLines()->pluck('quantity', 'fulfilment_method')->all())
        ->toBe([Pickup::KEY => 1, Shipping::KEY => 2])
        ->and($order->pickup_location_id)->toBe($this->warehouse->id)
        ->and($order->isPickup())->toBeTrue();

    $byMethod = $order->fulfilments()->with('lines.orderLine')->get()->keyBy('method');

    expect($byMethod->keys()->sort()->values()->all())->toBe([Pickup::KEY, Shipping::KEY])
        ->and($byMethod[Pickup::KEY]->lines->sole()->orderLine->fulfilment_method)->toBe(Pickup::KEY)
        ->and($byMethod[Shipping::KEY]->lines->sole()->quantity)->toBe(2);
});

test('pickup fulfilments are created at the order pickup location', function () {
    $counter = Location::factory()->create();
    $this->cart->add(orderVariant($this->currency));

    $order = place(readyForOrder($this->cart, pickupLocation: $counter)->createOrder());

    expect($order->pickupLocation->id)->toBe($counter->id)
        ->and($order->fulfilments()->sole())
        ->method->toBe(Pickup::KEY)
        ->location_id->toBe($counter->id);
});

test('a delivery-only order has no pickup location', function () {
    $this->cart->add(orderVariant($this->currency));

    $order = place(readyForOrder($this->cart)->createOrder());

    expect($order->pickup_location_id)->toBeNull()
        ->and($order->isPickup())->toBeFalse()
        ->and($order->fulfilments()->sole())
        ->method->toBe(Shipping::KEY)
        ->location_id->toBe($this->warehouse->id);
});

test('the shipping line carries the option meta without a pickup flag', function () {
    $this->cart->add(orderVariant($this->currency));

    $order = readyForOrder($this->cart, pickupLocation: $this->warehouse)->createOrder();

    expect((array) $order->shippingLines()->sole()->meta)->toBe(['zone' => 'UK']);
});

test('lines stamped with an unregistered method fall back to the claim pass', function () {
    $order = Order::factory()->create(['placed_at' => null]);
    $line = OrderLine::factory()->create([
        'order_id' => $order->id, 'type' => 'physical', 'quantity' => 1, 'fulfilment_method' => 'gone',
    ]);

    expect(place($order)->fulfilments()->sole())
        ->method->toBe(Shipping::KEY)
        ->and($order->fulfilments()->sole()->lines()->sole()->order_line_id)->toBe($line->id);
});

test('creating the order again keeps the same line for the same method', function () {
    $this->cart->add(orderVariant($this->currency), fulfilmentMethod: Pickup::KEY);
    $cart = readyForOrder($this->cart);

    $first = $cart->createOrder();
    $again = $cart->refresh()->createOrder(allowMultipleOrders: true, orderIdToUpdate: $first->id);

    expect($again->id)->toBe($first->id)
        ->and($again->productLines()->count())->toBe(1);
});
