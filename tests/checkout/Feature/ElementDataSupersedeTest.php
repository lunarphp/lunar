<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Checkout\Contracts\CheckoutDriver;
use Lunar\Checkout\Session\ModelElementStore;
use Lunar\Core\Facades\CartSession;
use Lunar\Tests\Checkout\TestCase;
use Lunar\Tests\Checkout\Utils\CheckoutCart;

uses(TestCase::class, RefreshDatabase::class);

it('carries captured element data onto a superseding session', function () {
    $cart = CheckoutCart::orderable();
    CartSession::use($cart);

    $driver = app(CheckoutDriver::class);
    $first = $driver->resolveOrCreateSession($cart);

    (new ModelElementStore($first))->put('order-details', ['reference' => 'PO-4242']);

    /*
     * createSession() is the supersede path: unlike resolveOrCreateSession(),
     * which hands back an existing Open session, it always mints a new one and
     * supersedes any Open sibling on the same cart. What this exercises is
     * therefore an explicit supersede on one unchanged cart.
     *
     * It is NOT what a sign-in cart merge does. Lunar's merge makes the guest
     * cart the target (AssociateUser passes the live cart to MergeCart as
     * $target and the user's stored cart as $source), so the cart id survives
     * sign-in and the session is simply resumed — the bag never needs carrying
     * on that path. The gap this leaves untested: carry-over is keyed on
     * cart_reference, so if the live cart id ever does change under a session,
     * show()'s cart-swap branch resolves a session against the new cart, finds
     * no sibling to carry from, and the bag is dropped.
     */
    $second = $driver->createSession($cart);

    expect($second->uuid)->not->toBe($first->uuid)
        ->and($second->getElementData('order-details'))->toBe(['reference' => 'PO-4242']);
});
