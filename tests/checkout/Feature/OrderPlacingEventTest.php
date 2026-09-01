<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Lunar\Checkout\Contracts\CheckoutDriver;
use Lunar\Checkout\Events\OrderPlacing;
use Lunar\Core\Facades\CartSession;
use Lunar\Tests\Checkout\TestCase;
use Lunar\Tests\Checkout\Utils\CheckoutCart;

uses(TestCase::class, RefreshDatabase::class);

it('fires OrderPlacing exactly once on the synchronous path', function () {
    Event::fake([OrderPlacing::class]);

    $cart = CheckoutCart::orderable();
    CartSession::use($cart);

    $driver = app(CheckoutDriver::class);
    $session = $driver->resolveOrCreateSession($cart);

    $driver->complete($session, $session->cart_fingerprint);

    Event::assertDispatchedTimes(OrderPlacing::class, 1);
    Event::assertDispatched(OrderPlacing::class, fn (OrderPlacing $event): bool => $event->session->uuid === $session->uuid
        && $event->order->placed_at === null);
});

it('carries the adopted order when one already exists', function () {
    Event::fake([OrderPlacing::class]);

    $cart = CheckoutCart::orderable();
    CartSession::use($cart);

    $driver = app(CheckoutDriver::class);
    $session = $driver->resolveOrCreateSession($cart);

    // Pin for payment first, exactly as the real pay boundary does for a
    // gateway-backed method. This moves the session Open -> PaymentProcessing,
    // so complete() takes the non-sync branch below (and skips its
    // sync-only canCreateOrder() check, which a cart with a completed order
    // already on it would otherwise fail).
    $driver->assertReadyForPayment($session, $session->cart_fingerprint);

    // The webhook-first race: the gateway's authorize() already placed the
    // order before complete() (the confirmation step) runs. createOrder()
    // alone only produces a draft (Cart::draftOrder() filters
    // whereNull('placed_at')) — stamping placed_at here is what routes
    // complete()'s adoption through completedOrder() instead, so this
    // genuinely exercises the race rather than retracing the sync-path test.
    $order = $cart->createOrder();
    $order->update(['placed_at' => now()]);
    $placedAt = $order->fresh()->placed_at->toDateTimeString();

    $driver->complete($session, $session->cart_fingerprint);

    Event::assertDispatchedTimes(OrderPlacing::class, 1);
    Event::assertDispatched(OrderPlacing::class, fn (OrderPlacing $event): bool => $event->order->id === $order->id);

    // complete() must not re-stamp an order the gateway already placed.
    expect($order->fresh()->placed_at->toDateTimeString())->toBe($placedAt);
});

it('lets a real listener observe the order before it is placed', function () {
    // Event::fake captures a snapshot and asserts against it afterwards,
    // which tests the snapshot rather than the guarantee. This registers an
    // actual listener so it sees exactly what complete() hands it, at the
    // moment it hands it over.
    $seen = 'never called';

    Event::listen(OrderPlacing::class, function (OrderPlacing $event) use (&$seen): void {
        $seen = $event->order->placed_at;
    });

    $cart = CheckoutCart::orderable();
    CartSession::use($cart);

    $driver = app(CheckoutDriver::class);
    $session = $driver->resolveOrCreateSession($cart);

    $driver->complete($session, $session->cart_fingerprint);

    expect($seen)->toBeNull();
});
