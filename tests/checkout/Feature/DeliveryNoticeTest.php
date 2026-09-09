<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Checkout\Contracts\DeliveryNotice;
use Lunar\Core\Models\Cart;
use Lunar\Tests\Checkout\TestCase;
use Lunar\Tests\Checkout\Utils\CheckoutCart;

uses(TestCase::class, RefreshDatabase::class);

/**
 * Spec 0011 §H. Only the host knows why its zones offered no delivery, so a
 * bound DeliveryNotice is projected verbatim and an unbound one projects null.
 */
it('projects the bound delivery notice for the cart', function () {
    app()->instance(DeliveryNotice::class, new class implements DeliveryNotice
    {
        public function noticeFor(Cart $cart): ?string
        {
            return 'We do not deliver to '.$cart->shippingAddress?->postcode.' yet.';
        }
    });

    $cart = CheckoutCart::orderable();
    $session = CheckoutCart::session($cart);

    $this->get(route('lunar.checkout.show', $session->uuid), ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertJsonPath('props.checkout.deliveryNotice', 'We do not deliver to SE1 1AA yet.');
});

it('projects no notice when the host binds none', function () {
    $cart = CheckoutCart::orderable();
    $session = CheckoutCart::session($cart);

    $this->get(route('lunar.checkout.show', $session->uuid), ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertJsonPath('props.checkout.deliveryNotice', null);
});
