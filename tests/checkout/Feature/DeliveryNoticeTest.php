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

/**
 * The notice is only half the answer: the pages have to stop offering a pay
 * button that cannot work, and say so where the customer is looking. These
 * are source assertions for the same reason OnAccountMethodTest's are — the
 * checkout app is a built Inertia bundle with no JS test runner behind it.
 */
it('blocks and explains the CTA on both pages when nothing delivers', function () {
    $js = dirname(__DIR__, 3).'/packages/checkout/resources/js';

    $store = file_get_contents($js.'/composables/useCheckout.js');
    $page = file_get_contents($js.'/components/LunarCheckout.vue');
    $express = file_get_contents($js.'/pages/ExpressConfirm.vue');
    $summary = file_get_contents($js.'/components/OrderSummary.vue');

    expect($store)->toContain('const deliveryUnavailable = computed(')
        // Pay answers with the reason rather than letting the boundary refuse.
        ->and($store)->toContain('state.payError = deliveryUnavailableMessage.value')
        ->and($page)->toContain('v-else-if="deliveryUnavailable"')
        ->and(substr_count($page, 'collectUnavailable || deliveryUnavailable'))->toBe(2)
        ->and(substr_count($express, 'collectUnavailable || deliveryUnavailable'))->toBe(2)
        ->and($express)->toContain('payError.value = deliveryUnavailableMessage.value')
        // A zero charge because nothing delivers is not free delivery.
        ->and($summary)->toContain('<span v-if="deliveryUnavailable" class="v ship-none">Unavailable</span>');
});
