<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Checkout\Contracts\CheckoutDriver;
use Lunar\Core\Facades\CartSession;
use Lunar\Tests\Checkout\TestCase;
use Lunar\Tests\Checkout\Utils\CheckoutCart;

uses(TestCase::class, RefreshDatabase::class);

/**
 * The billing address is a fingerprint input (spec 0010 §D), and pay() writes
 * it via XHR moments before pinning the session against the fingerprint the
 * client is holding. If the write did not hand back the post-write
 * fingerprint, every first pay on a session would fail the pay boundary with
 * fingerprint_mismatch: the client would still be pinning the page-load
 * fingerprint from before the billing address existed.
 */
function billingPayload(): array
{
    return [
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'company_name' => null,
        'line1' => '12 Analytical Row',
        'line2' => null,
        'city' => 'London',
        'state' => null,
        'postcode' => 'SW1A 2AA',
        'country_code' => 'GB',
        'phone' => null,
    ];
}

it('returns the post-write fingerprint to a json caller', function () {
    $cart = CheckoutCart::orderable();
    CartSession::use($cart);

    $driver = app(CheckoutDriver::class);
    $session = $driver->resolveOrCreateSession($cart);

    $before = $driver->fingerprint($session);

    $response = $this->postJson(route('lunar.checkout.billing-address.store', $session->uuid), billingPayload());

    $response->assertOk()->assertJsonStructure(['fingerprint']);

    $returned = $response->json('fingerprint');

    // The write changed the live fingerprint, and the caller got the new one:
    // pinning the returned value at the pay boundary must succeed.
    expect($returned)->not->toBe($before)
        ->and($returned)->toBe($driver->fingerprint($session));
});

it('still redirects an inertia caller back', function () {
    $cart = CheckoutCart::orderable();
    CartSession::use($cart);

    $session = app(CheckoutDriver::class)->resolveOrCreateSession($cart);

    $this->from('/checkout/'.$session->uuid)
        ->post(route('lunar.checkout.billing-address.store', $session->uuid), billingPayload())
        ->assertRedirect('/checkout/'.$session->uuid);
});
