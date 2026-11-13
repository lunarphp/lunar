<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Drivers\FulfilmentMethods\Pickup;
use Lunar\Core\Drivers\FulfilmentMethods\Shipping;
use Lunar\Core\Exceptions\FulfilmentException;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderAddress;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\States\Fulfilment\Pending;
use Lunar\Core\States\Fulfilment\Shipped;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true]);
    $this->warehouse = Location::factory()->create(['default' => true]);

    $this->order = Order::factory()->create(['placed_at' => null]);
    $this->line = OrderLine::factory()->create([
        'order_id' => $this->order->id,
        'type' => 'physical',
        'quantity' => 3,
        'purchasable_type' => (new ProductVariant)->getMorphClass(),
        'purchasable_id' => ProductVariant::factory()->create()->id,
    ]);
});

function shippingFulfilment(Order $order, OrderLine $line): Fulfilment
{
    return $order->createFulfilment([$line->id => $line->quantity], ['method' => Shipping::KEY]);
}

test('a delivery can be changed to pickup in full', function () {
    $source = shippingFulfilment($this->order, $this->line);
    $counter = Location::factory()->create();

    $pickup = $source->changeMethod(Pickup::KEY, $counter);

    expect($pickup->method)->toBe(Pickup::KEY)
        ->and($pickup->location_id)->toBe($counter->id)
        ->and($pickup->state)->toBeInstanceOf(Pending::class)
        ->and($pickup->lines()->sole()->quantity)->toBe(3)
        ->and(Fulfilment::find($source->id))->toBeNull()
        ->and($this->order->refresh()->isPickup())->toBeTrue()
        ->and($this->line->refresh()->fulfilment_method)->toBeNull();
});

test('part of a delivery can be changed to pickup', function () {
    $source = shippingFulfilment($this->order, $this->line);

    $pickup = $source->changeMethod(Pickup::KEY, moves: [$this->line->id => 1]);

    expect($pickup->lines()->sole()->quantity)->toBe(1)
        ->and($source->refresh()->lines()->sole()->quantity)->toBe(2)
        ->and($pickup->location_id)->toBe($this->warehouse->id);
});

test('a pickup defaults to the order pickup location', function () {
    $counter = Location::factory()->create();
    $this->order->update(['pickup_location_id' => $counter->id]);

    $pickup = shippingFulfilment($this->order, $this->line)->changeMethod(Pickup::KEY);

    expect($pickup->location_id)->toBe($counter->id);
});

test('a pickup changed to delivery needs a shipping address', function () {
    $source = $this->order->createFulfilment([$this->line->id => 3], ['method' => Pickup::KEY]);

    $source->changeMethod(Shipping::KEY);
})->throws(FulfilmentException::class, 'The order needs a shipping address before this fulfilment can be delivered.');

test('a pickup can be changed to delivery when the order has a shipping address', function () {
    OrderAddress::factory()->create(['order_id' => $this->order->id, 'type' => 'shipping']);
    $source = $this->order->createFulfilment([$this->line->id => 3], ['method' => Pickup::KEY]);

    $delivery = $source->changeMethod(Shipping::KEY);

    expect($delivery->method)->toBe(Shipping::KEY)
        ->and($this->order->refresh()->isPickup())->toBeFalse();
});

test('a collection-only item cannot be changed to delivery', function () {
    OrderAddress::factory()->create(['order_id' => $this->order->id, 'type' => 'shipping']);
    $this->line->purchasable->update(['fulfilment_methods' => [Pickup::KEY]]);
    $source = $this->order->createFulfilment([$this->line->id => 3], ['method' => Pickup::KEY]);

    $source->changeMethod(Shipping::KEY);
})->throws(FulfilmentException::class);

test('a handed-over fulfilment cannot change method', function () {
    $source = shippingFulfilment($this->order, $this->line);
    $source->update(['state' => Shipped::$name]);

    $source->refresh()->changeMethod(Pickup::KEY);
})->throws(FulfilmentException::class, 'Only pending or in-progress fulfilments can change method.');

test('moving more than the fulfilment carries is rejected', function () {
    shippingFulfilment($this->order, $this->line)->changeMethod(Pickup::KEY, moves: [$this->line->id => 4]);
})->throws(FulfilmentException::class);
