<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Checkout\Contracts\ExclusivePaymentMethod;
use Lunar\Checkout\Contracts\GuardsPayment;
use Lunar\Checkout\PaymentMethods\OnAccount;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Customer;
use Lunar\Tests\Checkout\TestCase;
use Lunar\Tests\Checkout\Utils\CheckoutCart;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    OnAccount::reset();
});

afterEach(fn () => OnAccount::reset());

function accountCart(?string $accountRef = 'ACC-1234'): Cart
{
    $cart = CheckoutCart::orderable();
    $customer = Customer::factory()->create(['account_ref' => $accountRef]);
    $cart->forceFill(['customer_id' => $customer->id])->save();

    return $cart->refresh();
}

it('is never available until a host says who qualifies', function () {
    expect((new OnAccount)->isAvailable(accountCart()))->toBeFalse();
});

it('is available exactly when the host closure says so', function () {
    OnAccount::eligibleWhen(fn (Cart $cart): bool => filled($cart->customer?->account_ref));

    expect((new OnAccount)->isAvailable(accountCart()))->toBeTrue()
        ->and((new OnAccount)->isAvailable(accountCart(null)))->toBeFalse()
        ->and((new OnAccount)->isAvailable(CheckoutCart::orderable()))->toBeFalse();
});

it('is synchronous, exclusive, and drives the offline payment type', function () {
    $method = new OnAccount;

    expect($method->handle())->toBe('on-account')
        ->and($method->driver())->toBe('offline')
        ->and($method->requiresIntent())->toBeFalse()
        ->and($method->component())->toBe('on-account-notice')
        ->and($method->label())->toBe('Pay on account')
        ->and($method)->toBeInstanceOf(ExclusivePaymentMethod::class)
        ->and($method)->toBeInstanceOf(GuardsPayment::class);
});

it('does not block pay by default', function () {
    $cart = accountCart();
    $session = CheckoutCart::session($cart);

    expect((new OnAccount)->paymentBlocker($session, $cart))->toBeNull();
});

it('blocks pay until a purchase order reference is entered when the host requires one', function () {
    OnAccount::requireReference();
    $cart = accountCart();
    $session = CheckoutCart::session($cart);

    expect((new OnAccount)->paymentBlocker($session, $cart))
        ->toBe('Enter your purchase order reference to place this order on account.');

    $session->putElementData('order-details', ['reference' => 'PO-4471', 'notes' => '']);

    expect((new OnAccount)->paymentBlocker($session->refresh(), $cart))->toBeNull();
});

it('wires the on-account notice and the payment blocker into the checkout app', function () {
    $base = dirname(__DIR__, 3).'/packages/checkout/resources/js';

    $app = file_get_contents($base.'/app.js');
    $checkout = file_get_contents($base.'/components/LunarCheckout.vue');
    $composable = file_get_contents($base.'/composables/useCheckout.js');

    expect($app)->toContain("registerCheckoutElement('on-account-notice', OnAccountNotice)")
        ->and(file_exists($base.'/components/payments/OnAccountNotice.vue'))->toBeTrue()
        ->and($composable)->toContain('const paymentBlocker = computed(')
        ->and($composable)->toContain('const payLabel = computed(')
        ->and(substr_count($checkout, ':disabled="state.processing || collectUnavailable || deliveryUnavailable || paymentBlocker !== null || !state.paymentMethods.length"'))->toBe(2)
        ->and(substr_count($checkout, '{{ payLabel }}'))->toBe(2);
});
