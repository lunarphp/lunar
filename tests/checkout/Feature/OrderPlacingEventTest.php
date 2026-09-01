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

    // The webhook-first race: the gateway's authorize() already made the order.
    $order = $cart->createOrder();

    $driver->complete($session, $session->cart_fingerprint);

    Event::assertDispatchedTimes(OrderPlacing::class, 1);
    Event::assertDispatched(OrderPlacing::class, fn (OrderPlacing $event): bool => $event->order->id === $order->id);
});
