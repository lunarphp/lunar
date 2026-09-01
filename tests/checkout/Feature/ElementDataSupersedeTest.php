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
     * supersedes any Open sibling on the same cart. That is what a sign-in
     * cart merge ends up doing.
     */
    $second = $driver->createSession($cart);

    expect($second->uuid)->not->toBe($first->uuid)
        ->and($second->getElementData('order-details'))->toBe(['reference' => 'PO-4242']);
});
