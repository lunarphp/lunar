<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Lunar\Checkout\Contracts\CheckoutDriver;
use Lunar\Core\Facades\CartSession;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Customer;
use Lunar\Tests\Checkout\TestCase;
use Lunar\Tests\Checkout\Utils\CheckoutCart;
use Lunar\Tests\Core\Stubs\User;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    config()->set('lunar.checkout.address_lookup.driver', 'ideal_postcodes');
    config()->set('lunar.checkout.address_lookup.ideal_postcodes.key', 'test-key');

    // The ownership test below deliberately has no Http::fake(): it asserts a
    // 403 fires before the vendor is ever touched. Without this guard, a
    // regression in ensureOwnership() would fall through to a real, billable
    // request against the vendor instead of failing the test loudly.
    Http::preventStrayRequests();
});

it('returns mapped addresses for an owned session', function () {
    Http::fake(['api.ideal-postcodes.co.uk/*' => Http::response([
        'code' => 2000,
        'result' => [[
            'line_1' => '10 Downing Street',
            'line_2' => '',
            'line_3' => '',
            'post_town' => 'LONDON',
            'county' => '',
            'postcode' => 'SW1A 2AA',
        ]],
    ])]);

    $cart = CheckoutCart::orderable();
    CartSession::use($cart);
    $session = app(CheckoutDriver::class)->resolveOrCreateSession($cart);

    $response = $this->postJson(route('lunar.checkout.address-lookup', $session->uuid), [
        'postcode' => 'SW1A 2AA',
    ]);

    $response->assertOk()
        ->assertJsonPath('addresses.0.line1', '10 Downing Street')
        ->assertJsonPath('addresses.0.city', 'LONDON')
        ->assertJsonPath('addresses.0.countryCode', 'GB');
});

it('rejects a malformed postcode without calling the vendor', function () {
    Http::fake();

    $cart = CheckoutCart::orderable();
    CartSession::use($cart);
    $session = app(CheckoutDriver::class)->resolveOrCreateSession($cart);

    $this->postJson(route('lunar.checkout.address-lookup', $session->uuid), [
        'postcode' => 'not a postcode',
    ])->assertStatus(422);

    Http::assertNothingSent();
});

it('forbids a session belonging to another customer', function () {
    $cart = CheckoutCart::orderable();
    CartSession::use($cart);
    $session = app(CheckoutDriver::class)->resolveOrCreateSession($cart);

    $session->update(['customer_reference' => (string) Customer::factory()->create()->id]);

    $intruder = User::factory()->create();
    $intruderCustomer = Customer::factory()->create();
    $intruder->customers()->attach($intruderCustomer);

    // A different cart than the session's, so ensureOwnership falls through to
    // the customer_reference fallback rather than short-circuiting on a cart
    // id the test session still has bound from CartSession::use() above.
    $otherCart = Cart::factory()->create([
        'channel_id' => $cart->channel_id,
        'currency_id' => $cart->currency_id,
    ]);
    CartSession::use($otherCart);

    $this->actingAs($intruder)
        ->postJson(route('lunar.checkout.address-lookup', $session->uuid), ['postcode' => 'SW1A 2AA'])
        ->assertForbidden();
});

it('returns a generic failure and never leaks the vendor body', function () {
    Http::fake(['api.ideal-postcodes.co.uk/*' => Http::response('SECRET UPSTREAM DETAIL', 500)]);

    $cart = CheckoutCart::orderable();
    CartSession::use($cart);
    $session = app(CheckoutDriver::class)->resolveOrCreateSession($cart);

    $response = $this->postJson(route('lunar.checkout.address-lookup', $session->uuid), [
        'postcode' => 'SW1A 2AA',
    ]);

    $response->assertStatus(503);
    expect($response->getContent())->not->toContain('SECRET UPSTREAM DETAIL');
});

it('projects the lookup url when a driver can answer', function () {
    $cart = CheckoutCart::orderable();
    CartSession::use($cart);
    $session = app(CheckoutDriver::class)->resolveOrCreateSession($cart);

    // X-Inertia makes show() return the prop payload as JSON, so the
    // projection is asserted without needing the built app manifest (see
    // CheckoutRouteTest's merchant-projection tests for the same pattern —
    // assertInertia() needs the Blade-rendered view, which needs the
    // published Vite manifest that isn't built in this test environment).
    $this->get(route('lunar.checkout.show', $session->uuid), ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertJsonPath(
            'props.checkout.urls.addressLookup',
            route('lunar.checkout.address-lookup', $session->uuid),
        );
});

it('projects a null lookup url under the null driver', function () {
    config()->set('lunar.checkout.address_lookup.driver', 'null');

    $cart = CheckoutCart::orderable();
    CartSession::use($cart);
    $session = app(CheckoutDriver::class)->resolveOrCreateSession($cart);

    $this->get(route('lunar.checkout.show', $session->uuid), ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertJsonPath('props.checkout.urls.addressLookup', null);
});
