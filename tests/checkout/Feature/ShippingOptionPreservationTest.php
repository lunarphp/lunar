<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Checkout\Contracts\CheckoutDriver;
use Lunar\Tests\Checkout\TestCase;
use Lunar\Tests\Checkout\Utils\CheckoutCart;

uses(TestCase::class, RefreshDatabase::class);

it('keeps the chosen shipping option when the shipping address is rewritten', function () {
    $cart = CheckoutCart::orderable();
    $session = CheckoutCart::session($cart);

    expect(app(CheckoutDriver::class)->getSelectedShippingOption($session))->toBe('standard');

    // Lunar recreates the address row on every write and the chosen option
    // rides on that row; an edit made after a rate was picked (the express
    // confirm page's name field, say) must not strip the selection and fail
    // order creation at pay.
    $this->post(route('lunar.checkout.shipping-address.store', $session->uuid), [
        'first_name' => 'Alec',
        'last_name' => 'Ritson',
        'line1' => '1 Trade Counter Way',
        'city' => 'London',
        'postcode' => 'SE1 1AA',
        'country_code' => 'GB',
    ])->assertRedirect();

    expect(app(CheckoutDriver::class)->getSelectedShippingOption($session->refresh()))->toBe('standard');
});
