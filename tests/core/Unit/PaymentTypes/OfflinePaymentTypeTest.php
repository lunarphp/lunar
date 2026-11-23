<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Lunar\Core\DataObjects\PaymentAuthorize;
use Lunar\Core\Enums\PaymentTerms;
use Lunar\Core\Exceptions\Carts\CartException;
use Lunar\Core\Facades\Payments;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartAddress;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Transaction;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

test('can authorize payment', function () {
    $cart = Cart::factory()->create();

    Config::set('lunar.payments.types.offline', [
        'authorized' => 'in-process',
    ]);

    CartAddress::factory()->create([
        'cart_id' => $cart->id,
        'type' => 'billing',
        'country_id' => Country::factory(),
        'first_name' => 'Santa',
        'line_one' => '123 Elf Road',
        'city' => 'Lapland',
        'postcode' => 'BILL',
    ]);

    CartAddress::factory()->create([
        'cart_id' => $cart->id,
        'type' => 'shipping',
        'country_id' => Country::factory(),
        'first_name' => 'Santa',
        'line_one' => '123 Elf Road',
        'city' => 'Lapland',
        'postcode' => 'SHIPP',
    ]);

    $result = Payments::driver('offline')->cart($cart->refresh())->authorize();

    expect($result)->toBeInstanceOf(PaymentAuthorize::class);
    expect($result->success)->toBeTrue();

    expect($cart->refresh()->completedOrder)->toBeInstanceOf(Order::class);
});

test('can set additional meta', function () {
    $cart = Cart::factory()->create();

    Config::set('lunar.payments.types.offline', [
        'authorized' => 'in-process',
    ]);

    CartAddress::factory()->create([
        'cart_id' => $cart->id,
        'type' => 'billing',
        'country_id' => Country::factory(),
        'first_name' => 'Santa',
        'line_one' => '123 Elf Road',
        'city' => 'Lapland',
        'postcode' => 'BILL',
    ]);

    CartAddress::factory()->create([
        'cart_id' => $cart->id,
        'type' => 'shipping',
        'country_id' => Country::factory(),
        'first_name' => 'Santa',
        'line_one' => '123 Elf Road',
        'city' => 'Lapland',
        'postcode' => 'SHIPP',
    ]);

    Payments::driver('offline')->cart($cart->refresh())->withData([
        'meta' => [
            'foo' => 'bar',
        ],
    ])->authorize();

    $order = $cart->refresh()->completedOrder;

    $meta = (array) $order->meta;

    expect($meta['foo'])->toEqual('bar');
});

function offlineCartWithAddresses(): Cart
{
    $cart = Cart::factory()->create();

    foreach (['billing', 'shipping'] as $type) {
        CartAddress::factory()->create([
            'cart_id' => $cart->id,
            'type' => $type,
            'country_id' => Country::factory(),
            'first_name' => 'Santa',
            'line_one' => '123 Elf Road',
            'city' => 'Lapland',
            'postcode' => 'NP1',
        ]);
    }

    return $cart->refresh();
}

test('a payment type with terms places the order on those terms', function () {
    Config::set('lunar.payments.types.on-account', [
        'driver' => 'offline',
        'terms' => 'on-account',
    ]);

    $cart = offlineCartWithAddresses();

    $driver = Payments::driver('on-account');

    expect($driver->terms())->toBe(PaymentTerms::OnAccount);

    $driver->cart($cart)->authorize();

    $order = $cart->refresh()->completedOrder;

    expect($order->payment_terms)->toBe(PaymentTerms::OnAccount)
        ->and($order->isAwaitingPaymentByTerms())->toBeTrue();
});

test('a payment type without terms keeps the cart terms', function () {
    Config::set('lunar.payments.types.offline', []);

    $cart = offlineCartWithAddresses();
    $cart->setPaymentTerms(PaymentTerms::OnAccount);

    $driver = Payments::driver('offline');

    expect($driver->terms())->toBeNull();

    $driver->cart($cart)->authorize();

    expect($cart->refresh()->completedOrder->payment_terms)->toBe(PaymentTerms::OnAccount);
});

test('the payment type terms win over the cart terms', function () {
    Config::set('lunar.payments.types.on-account', [
        'driver' => 'offline',
        'terms' => 'on-account',
    ]);

    $cart = offlineCartWithAddresses();
    $cart->setPaymentTerms(PaymentTerms::OnPickup);

    Payments::driver('on-account')->cart($cart)->authorize();

    expect($cart->refresh()->completedOrder->payment_terms)->toBe(PaymentTerms::OnAccount);
});

test('an order placed without terms is on checkout terms', function () {
    $cart = offlineCartWithAddresses();

    Payments::driver('offline')->cart($cart)->authorize();

    expect($cart->refresh()->completedOrder->payment_terms)->toBe(PaymentTerms::Checkout);
});

test('pickup terms from the payment type are validated against the cart', function () {
    Config::set('lunar.payments.types.pay-on-pickup', [
        'driver' => 'offline',
        'terms' => 'on-pickup',
    ]);

    Payments::driver('pay-on-pickup')->cart(offlineCartWithAddresses())->authorize();
})->throws(CartException::class, 'Pay on pickup needs a pickup shipping option');

test('delivery terms from the payment type are validated against the cart', function () {
    Config::set('lunar.payments.types.cash-on-delivery', [
        'driver' => 'offline',
        'terms' => 'on-delivery',
    ]);

    Payments::driver('cash-on-delivery')->cart(offlineCartWithAddresses())->authorize();
})->throws(CartException::class, 'Pay on delivery needs a delivery shipping option');

test('capturing an offline intent records the capture transaction', function () {
    $order = Order::factory()->create(['total' => 12000]);
    $intent = Transaction::factory()->for($order)->create([
        'type' => 'intent',
        'success' => true,
        'driver' => 'offline',
        'amount' => 12000,
        'card_type' => 'visa',
        'last_four' => '4242',
    ]);

    $result = $intent->capture(5000);

    $capture = $order->transactions()->whereType('capture')->sole();

    expect($result->success)->toBeTrue()
        ->and($capture->success)->toBeTruthy()
        ->and($capture->amount)->toBe(5000)
        ->and($capture->driver)->toBe('offline')
        ->and($capture->parent_transaction_id)->toBe($intent->id)
        ->and($capture->last_four)->toBe('4242')
        ->and($capture->captured_at)->not->toBeNull();
});

test('capturing an offline intent without an amount captures the whole authorization', function () {
    $order = Order::factory()->create(['total' => 12000]);
    $intent = Transaction::factory()->for($order)->create([
        'type' => 'intent',
        'success' => true,
        'driver' => 'offline',
        'amount' => 12000,
    ]);

    $intent->capture();

    expect($order->transactions()->whereType('capture')->sole()->amount)->toBe(12000);
});
