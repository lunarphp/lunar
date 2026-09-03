<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Checkout\Contracts\CheckoutDriver;
use Lunar\Core\Facades\CartSession;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Customer;
use Lunar\Tests\Checkout\TestCase;
use Lunar\Tests\Checkout\Utils\CheckoutCart;
use Lunar\Tests\Core\Stubs\User;

uses(TestCase::class, RefreshDatabase::class);

it('quotes rates for a candidate address without persisting anything', function () {
    $cart = CheckoutCart::orderable();
    $session = CheckoutCart::session($cart);
    $before = CheckoutCart::fingerprint($session);

    $response = $this->postJson(route('lunar.checkout.shipping-quote', $session->uuid), [
        'postcode' => 'ME14 1XX',
        'country_code' => 'GB',
        'city' => 'Maidstone',
    ]);

    $response->assertOk()->assertJsonStructure([
        'methods' => [['id', 'name', 'sub', 'price', 'collect']],
        'totals' => ['sub_total', 'shipping_total', 'tax_total', 'total'],
    ]);

    // Nothing written: address and fingerprint unchanged.
    $this->get(route('lunar.checkout.show', $session->uuid));

    expect(CheckoutCart::fingerprint($session->refresh()))->toBe($before);
    expect(app(CheckoutDriver::class)->getShippingAddress($session)->postcode)->toBe('SE1 1AA');
});

it('quotes totals for a candidate option', function () {
    $cart = CheckoutCart::orderable();
    $session = CheckoutCart::session($cart);

    $response = $this->postJson(route('lunar.checkout.shipping-quote', $session->uuid), [
        'postcode' => 'ME14 1XX',
        'country_code' => 'GB',
        'shipping_option' => 'standard',
    ]);

    $response->assertOk();

    expect($response->json('totals.shipping_total'))->toBeGreaterThanOrEqual(0);
});

it('is ownership gated', function () {
    $cart = CheckoutCart::orderable();
    $session = CheckoutCart::session($cart);

    $session->update(['customer_reference' => (string) Customer::factory()->create()->id]);

    $intruder = User::factory()->create();
    $intruderCustomer = Customer::factory()->create();
    $intruder->customers()->attach($intruderCustomer);

    $otherCart = Cart::factory()->create([
        'channel_id' => $cart->channel_id,
        'currency_id' => $cart->currency_id,
    ]);
    CartSession::use($otherCart);

    $this->actingAs($intruder)
        ->postJson(route('lunar.checkout.shipping-quote', $session->uuid), [
            'postcode' => 'ME14 1XX',
            'country_code' => 'GB',
        ])
        ->assertForbidden();
});

it('throttles the shipping quote to 10 requests per minute per IP', function () {
    $cart = CheckoutCart::orderable();
    $session = CheckoutCart::session($cart);

    for ($i = 0; $i < 10; $i++) {
        $this->postJson(route('lunar.checkout.shipping-quote', $session->uuid), [
            'postcode' => 'ME14 1XX',
            'country_code' => 'GB',
        ])->assertOk();
    }

    $this->postJson(route('lunar.checkout.shipping-quote', $session->uuid), [
        'postcode' => 'ME14 1XX',
        'country_code' => 'GB',
    ])->assertStatus(429);
});
