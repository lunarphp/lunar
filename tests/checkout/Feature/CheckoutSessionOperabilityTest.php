<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Checkout\Contracts\CheckoutDriver;
use Lunar\Checkout\States\CheckoutSession\Completed;
use Lunar\Core\Facades\CartSession;
use Lunar\Tests\Checkout\TestCase;
use Lunar\Tests\Checkout\Utils\CheckoutCart;

uses(TestCase::class, RefreshDatabase::class);

/*
 * ensureOwnership() says who may touch a session; ensureOperable() (added
 * alongside show()'s existing terminal-state redirects) says whether it is
 * still touchable at all. Both cases here use the session's own cart, so
 * ownership passes trivially — that's deliberate: it isolates the
 * operability gate rather than retracing the ownership tests already in
 * ElementDataStoreTest and AddressLookupRouteTest.
 */
it('rejects an element write to a completed session', function () {
    $cart = CheckoutCart::orderable();
    CartSession::use($cart);
    $session = app(CheckoutDriver::class)->resolveOrCreateSession($cart);

    $session->update([
        'status' => Completed::$name,
        'active_cart_reference' => null,
        'order_reference' => '1',
        'completed_at' => now(),
    ]);

    $this->post(route('lunar.checkout.elements.store', ['session' => $session->uuid, 'handle' => 'order-details']), [
        'reference' => 'PO-1',
    ])->assertStatus(409);

    expect($session->fresh()->getElementData('order-details'))->toBeNull();
});

it('rejects an element write to an expired session', function () {
    $cart = CheckoutCart::orderable();
    CartSession::use($cart);
    $session = app(CheckoutDriver::class)->resolveOrCreateSession($cart);

    $session->update(['expires_at' => now()->subHour()]);

    $this->post(route('lunar.checkout.elements.store', ['session' => $session->uuid, 'handle' => 'order-details']), [
        'reference' => 'PO-1',
    ])->assertStatus(409);

    expect($session->fresh()->getElementData('order-details'))->toBeNull();
});
