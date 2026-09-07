<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Checkout\Contracts\Actions\SetsFulfilment;
use Lunar\Checkout\Contracts\CheckoutDriver;
use Lunar\Checkout\DataTypes\CollectionPoint;
use Lunar\Checkout\Exceptions\PaymentConfirmationException;
use Lunar\Core\Facades\CartSession;
use Lunar\Tests\Checkout\TestCase;
use Lunar\Tests\Checkout\Utils\CheckoutCart;
use Lunar\Tests\Checkout\Utils\CollectionPointsStub;

uses(TestCase::class, RefreshDatabase::class);

afterEach(fn () => CollectionPointsStub::unbind());

it('blocks order creation while a required point is unchosen', function () {
    CollectionPointsStub::bind([new CollectionPoint('london', 'London'), new CollectionPoint('dartford', 'Dartford')]);
    $cart = app(SetsFulfilment::class)->execute(CheckoutCart::orderable(collect: true), 'collect');

    expect($cart->canCreateOrder())->toBeFalse();

    $cart = app(SetsFulfilment::class)->execute($cart, 'collect', 'london');

    expect($cart->canCreateOrder())->toBeTrue();
});

it('does not require a point when none are offered', function () {
    $cart = app(SetsFulfilment::class)->execute(CheckoutCart::orderable(collect: true), 'collect');

    expect($cart->canCreateOrder())->toBeTrue();
});

it('fails when the mode and the stored option disagree', function () {
    $cart = CheckoutCart::orderable(collect: true);
    $cart->meta = ['fulfilment' => 'delivery'];
    $cart->save();

    expect($cart->refresh()->canCreateOrder())->toBeFalse();
});

it('refuses payment with a specific reason when the point is missing', function () {
    CollectionPointsStub::bind([new CollectionPoint('london', 'London'), new CollectionPoint('dartford', 'Dartford')]);
    $cart = app(SetsFulfilment::class)->execute(CheckoutCart::orderable(collect: true), 'collect');
    CartSession::use($cart);

    $driver = app(CheckoutDriver::class);
    $session = $driver->resolveOrCreateSession($cart);

    try {
        $driver->assertReadyForPayment($session, $session->cart_fingerprint);
        $this->fail('expected a PaymentConfirmationException');
    } catch (PaymentConfirmationException $e) {
        expect($e->reason)->toBe('collection_point_required');
    }

    try {
        $driver->complete($session, $session->cart_fingerprint);
        $this->fail('expected a PaymentConfirmationException');
    } catch (PaymentConfirmationException $e) {
        expect($e->reason)->toBe('collection_point_required');
    }
});

it('changes the fingerprint when the mode or the point changes', function () {
    CollectionPointsStub::bind([new CollectionPoint('london', 'London'), new CollectionPoint('dartford', 'Dartford')]);
    $cart = CheckoutCart::orderable(collect: true);
    CartSession::use($cart);
    $driver = app(CheckoutDriver::class);
    $session = $driver->resolveOrCreateSession($cart);

    $before = $driver->fingerprint($session);
    app(SetsFulfilment::class)->execute($cart, 'collect', 'london');
    $withLondon = $driver->fingerprint($session);
    app(SetsFulfilment::class)->execute($cart->refresh(), 'collect', 'dartford');
    $withDartford = $driver->fingerprint($session);

    expect($withLondon)->not->toBe($before)
        ->and($withDartford)->not->toBe($withLondon);
});
