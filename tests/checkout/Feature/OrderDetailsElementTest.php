<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Checkout\Contracts\CheckoutDriver;
use Lunar\Checkout\Contracts\ElementRegistry;
use Lunar\Checkout\Elements\OrderDetails;
use Lunar\Checkout\Session\ModelElementStore;
use Lunar\Core\Facades\CartSession;
use Lunar\Tests\Checkout\TestCase;
use Lunar\Tests\Checkout\Utils\CheckoutCart;

uses(TestCase::class, RefreshDatabase::class);

it('describes itself as a main-region element', function () {
    $element = new OrderDetails;

    expect($element->handle())->toBe('order-details')
        ->and($element->component())->toBe('order-details')
        ->and($element->region())->toBe('main');
});

it('treats both fields as optional', function () {
    $cart = CheckoutCart::orderable();
    CartSession::use($cart);
    $session = app(CheckoutDriver::class)->resolveOrCreateSession($cart);

    app(ElementRegistry::class)->add(OrderDetails::class);

    $this->post(route('lunar.checkout.elements.store', ['session' => $session->uuid, 'handle' => 'order-details']), [])
        ->assertRedirect();

    expect($session->fresh()->getElementData('order-details'))->toBe([]);
});

it('persists a purchase order reference and notes onto the session row', function () {
    $cart = CheckoutCart::orderable();
    CartSession::use($cart);
    $session = app(CheckoutDriver::class)->resolveOrCreateSession($cart);

    app(ElementRegistry::class)->add(OrderDetails::class);

    $this->post(route('lunar.checkout.elements.store', ['session' => $session->uuid, 'handle' => 'order-details']), [
        'reference' => 'PO-99812',
        'notes' => 'Deliver to the rear gate, ask for Dave.',
    ])->assertRedirect();

    expect($session->fresh()->getElementData('order-details'))->toBe([
        'reference' => 'PO-99812',
        'notes' => 'Deliver to the rear gate, ask for Dave.',
    ]);
});

it('rejects an over-long reference', function () {
    $cart = CheckoutCart::orderable();
    CartSession::use($cart);
    $session = app(CheckoutDriver::class)->resolveOrCreateSession($cart);

    app(ElementRegistry::class)->add(OrderDetails::class);

    $this->post(route('lunar.checkout.elements.store', ['session' => $session->uuid, 'handle' => 'order-details']), [
        'reference' => str_repeat('X', 256),
    ])->assertSessionHasErrors('reference');
});

it('seeds the form from what was already captured', function () {
    $cart = CheckoutCart::orderable();
    CartSession::use($cart);
    $session = app(CheckoutDriver::class)->resolveOrCreateSession($cart);

    (new ModelElementStore($session))->put('order-details', ['reference' => 'PO-1']);

    $element = (new OrderDetails)->setDataStore(new ModelElementStore($session->fresh()));

    expect($element->data())->toBe(['reference' => 'PO-1']);
});
