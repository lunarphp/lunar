<?php

uses(\Lunar\Tests\Core\TestCase::class);

use Lunar\Base\ValueObjects\Cart\ShippingBreakdown;
use Lunar\Base\ValueObjects\Cart\ShippingBreakdownItem;
use Lunar\Base\ValueObjects\Cart\TaxBreakdown;
use Lunar\DataTypes\Price;
use Lunar\Models\Cart;
use Lunar\Models\Currency;
use Lunar\Models\Customer;
use Lunar\Models\Language;
use Lunar\Models\Order;
use Lunar\Models\OrderLine;
use Lunar\Models\ProductVariant;
use Lunar\Models\Transaction;
use Lunar\Tests\Core\Stubs\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create([
        'default' => true,
        'code' => 'en',
    ]);

    Currency::factory()->create([
        'default' => true,
        'decimal_places' => 2,
    ]);
});

test('can fetch cart relationship', function () {
    Currency::factory()->create([
        'default' => true,
    ]);
    $cart = Cart::factory()->create();

    $order = Order::factory()->create([
        'cart_id' => $cart->id,
        'user_id' => null,
    ]);

    expect($order->cart->id)->toEqual($cart->id);
});

test('can make an order', function () {
    Currency::factory()->create([
        'default' => true,
    ]);

    $order = Order::factory()->create([
        'user_id' => null,
    ]);

    $data = $order->getRawOriginal();

    $this->assertDatabaseHas((new Order())->getTable(), $data);
});

test('order has correct casting', function () {
    Currency::factory()->create([
        'default' => true,
    ]);

    $order = Order::factory()->create([
        'user_id' => null,
        'placed_at' => now(),
        'meta' => [
            'foo' => 'bar',
        ],
    ]);

    expect($order->meta)->toBeObject();
    expect($order->tax_breakdown)->toBeInstanceOf(TaxBreakdown::class);
    expect($order->placed_at)->toBeInstanceOf(DateTime::class);
});

test('can create lines', function () {
    Currency::factory()->create([
        'default' => true,
    ]);

    $order = Order::factory()->create([
        'user_id' => null,
        'placed_at' => now(),
        'meta' => [
            'foo' => 'bar',
        ],
    ]);

    expect($order->lines)->toHaveCount(0);

    $variant = ProductVariant::factory()->create();

    OrderLine::factory()->create([
        'purchasable_type' => $variant->getMorphClass(),
        'purchasable_id' => $variant->id,
        'order_id' => $order->id,
    ]);

    expect($order->refresh()->lines)->toHaveCount(1);
});

test('can update status', function () {
    $order = Order::factory()->create([
        'user_id' => null,
        'status' => 'status_a',
    ]);

    expect($order->status)->toEqual('status_a');

    $order->update([
        'status' => 'status_b',
    ]);

    expect($order->status)->toEqual('status_b');
});

test('can create transaction for order', function () {
    Currency::factory()->create([
        'default' => true,
    ]);

    $order = Order::factory()->create([
        'user_id' => null,
        'status' => 'status_a',
    ]);

    expect($order->transactions)->toHaveCount(0);

    $transaction = Transaction::factory()->make()->toArray();

    unset($transaction['currency']);

    $order->transactions()->create($transaction);

    expect($order->refresh()->transactions)->toHaveCount(1);
});

test('can retrieve different transaction types for order', function () {
    Currency::factory()->create([
        'default' => true,
    ]);

    $order = Order::factory()->create([
        'user_id' => null,
        'status' => 'status_a',
    ]);

    expect($order->transactions)->toHaveCount(0);

    $charge = Transaction::factory()->create([
        'order_id' => $order->id,
        'amount' => 200,
        'type' => 'capture',
    ]);

    $refund = Transaction::factory()->create([
        'order_id' => $order->id,
        'type' => 'refund',
    ]);

    $order = $order->refresh();

    expect($order->transactions)->toHaveCount(2);

    expect($order->captures)->toHaveCount(1);
    expect($order->refunds)->toHaveCount(1);

    expect($order->captures->first()->id)->toEqual($charge->id);
    expect($order->refunds->first()->id)->toEqual($refund->id);
});

test('can have user and customer associated', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@domain.com',
        'email_verified_at' => now(),
        'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        'remember_token' => \Illuminate\Support\Str::random(10),
    ]);

    $customer = $user->customers()->create(
        Customer::factory()->make()->toArray()
    );

    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'user_id' => $user->getKey(),
    ]);

    expect($order->customer->id)->toEqual($customer->id);
    expect($order->user->getKey())->toEqual($user->getKey());
});

test('can check order is placed', function () {
    Currency::factory()->create([
        'default' => true,
    ]);

    $order = Order::factory()->create([
        'user_id' => null,
    ]);

    expect($order->isDraft())->toBeTrue();

    $order->placed_at = now();

    expect($order->isPlaced())->toBeTrue();
});

test('can cast and store shipping breakdown', function () {
    $order = Order::factory()->create();

    $breakdown = new ShippingBreakdown(
        items: collect([
            new ShippingBreakdownItem(
                name: 'Breakdown A',
                identifier: 'BA',
                price: $shippingPrice = new Price(123, $currency = Currency::getDefault(), 1)
            ),
        ])
    );

    $order->shipping_breakdown = $breakdown;

    $order->save();

    $this->assertDatabaseHas((new Order)->getTable(), [
        'shipping_breakdown' => json_encode([[
            'name' => 'Breakdown A',
            'identifier' => 'BA',
            'value' => 123,
            'formatted' => $shippingPrice->formatted,
            'currency' => $currency->toArray(),
        ]]),
    ]);

    $breakdown = $order->refresh()->shipping_breakdown;

    expect($breakdown->items)->toHaveCount(1);

    $breakdownItem = $breakdown->items->first();

    expect($breakdownItem->name)->toEqual('Breakdown A');
    expect($breakdownItem->identifier)->toEqual('BA');
    expect($breakdownItem->price)->toBeInstanceOf(Price::class);
    expect($breakdownItem->price->value)->toEqual(123);
});
