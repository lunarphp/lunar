<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Lunar\Core\Facades\CartSession;
use Lunar\Core\Models\CartAddress;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Region;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

function defaultRegion(): Region
{
    Channel::factory()->create(['default' => true]);
    Currency::factory()->create(['default' => true]);
    $language = Language::factory()->create(['default' => true]);

    return Region::factory()->create([
        'default' => true,
        'channel_id' => Channel::getDefault()->id,
        'currency_id' => Currency::getDefault()->id,
        'language_id' => $language->id,
    ]);
}

test('a new cart is stamped with the default region', function () {
    $region = defaultRegion();

    Config::set('lunar.cart_session.auto_create', true);

    $cart = CartSession::current();

    expect($cart->region_id)->toBe($region->id);
    expect($cart->region->id)->toBe($region->id);
});

test('an order created from a cart carries the cart region', function () {
    $region = defaultRegion();

    Config::set('lunar.cart_session.auto_create', true);

    $cart = CartSession::current();

    $shipping = CartAddress::factory()->create(['cart_id' => $cart->id, 'type' => 'shipping']);
    $billing = CartAddress::factory()->create(['cart_id' => $cart->id, 'type' => 'billing']);
    $cart->setShippingAddress($shipping);
    $cart->setBillingAddress($billing);

    $order = CartSession::createOrder();

    expect($order->region_id)->toBe($region->id);
});
