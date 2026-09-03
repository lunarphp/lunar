<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Checkout\Contracts\CheckoutDriver;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\DataTypes\ShippingOption;
use Lunar\Core\Facades\CartSession;
use Lunar\Core\Facades\ShippingManifest;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\TaxClass;
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

it('prices the auto-selected first rate into a quote with no chosen option', function () {
    $cart = CheckoutCart::orderable(collect: true);
    $session = CheckoutCart::session($cart);

    // The only deliverable rate is priced. The Express Checkout Element shows
    // it first and auto-selects it without firing shippingratechange, so a
    // quote with no chosen option must already carry its cost.
    ShippingManifest::addOption(new ShippingOption(
        name: 'Express 48',
        description: 'Two working days',
        identifier: 'express-48',
        price: new PriceValue(495, Currency::query()->firstWhere('default', true)),
        taxClass: TaxClass::query()->firstWhere('default', true),
    ));

    $response = $this->postJson(route('lunar.checkout.shipping-quote', $session->uuid), [
        'postcode' => 'ME14 1XX',
        'country_code' => 'GB',
    ]);

    $response->assertOk();

    $totals = $response->json('totals');

    expect($totals['shipping_total'])->toBe(495);

    // Tax-exclusive rows sum to the tax-inclusive total.
    expect($totals['total'])->toBe(
        $totals['sub_total'] + $totals['shipping_total'] + $totals['tax_total'] - $totals['discount_total'],
    );

    // Rolled back: the cart's real selection is untouched.
    expect(app(CheckoutDriver::class)->getSelectedShippingOption($session))->toBe('collection');
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
