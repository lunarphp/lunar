<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Checkout\Contracts\PaymentMethodRegistry;
use Lunar\Checkout\PaymentMethods\AbstractPaymentMethod;
use Lunar\Checkout\PaymentMethods\OnAccount;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Customer;
use Lunar\Tests\Checkout\TestCase;
use Lunar\Tests\Checkout\Utils\CheckoutCart;

uses(TestCase::class, RefreshDatabase::class);

/**
 * A non-exclusive, gateway-backed method standing in for card payments:
 * individually available on every cart, but excluded once OnAccount qualifies
 * (spec 0014 §B). Requires an intent, so it also exercises the
 * /payment-intent boundary.
 */
class NonExclusiveCardMethod extends AbstractPaymentMethod
{
    public function handle(): string
    {
        return 'card';
    }

    public function label(): string
    {
        return 'Card';
    }

    public function driver(): string
    {
        return 'offline';
    }

    public function requiresIntent(): bool
    {
        return true;
    }

    public function component(): string
    {
        return 'card';
    }
}

beforeEach(function () {
    OnAccount::reset();
});

afterEach(fn () => OnAccount::reset());

function accountEligibleCart(): Cart
{
    OnAccount::eligibleWhen(fn (Cart $cart): bool => filled($cart->customer?->account_ref));

    $cart = CheckoutCart::orderable();
    $customer = Customer::factory()->create(['account_ref' => 'ACC-1234']);
    $cart->forceFill(['customer_id' => $customer->id])->save();

    return $cart->refresh();
}

it('refuses card at the pay boundary once on-account qualifies and excludes it', function () {
    app(PaymentMethodRegistry::class)->add(NonExclusiveCardMethod::class)->add(OnAccount::class);

    $cart = accountEligibleCart();
    $session = CheckoutCart::session($cart);

    $this->postJson(route('lunar.checkout.pay', $session->uuid), [
        'fingerprint' => CheckoutCart::fingerprint($session),
        'payment_method' => 'card',
    ])->assertStatus(422)
        ->assertJsonPath('errors.payment_method.0', 'The selected payment method is not available.');
});

it('refuses card at the payment-intent boundary once on-account qualifies and excludes it', function () {
    app(PaymentMethodRegistry::class)->add(NonExclusiveCardMethod::class)->add(OnAccount::class);

    $cart = accountEligibleCart();
    $session = CheckoutCart::session($cart);

    $this->postJson(route('lunar.checkout.payment-intent.store', $session->uuid), [
        'payment_method' => 'card',
    ])->assertStatus(422)
        ->assertJsonPath('errors.payment_method.0', 'The selected payment method is not available.');
});

it('refuses on-account at the pay boundary when the cart does not qualify', function () {
    app(PaymentMethodRegistry::class)->add(NonExclusiveCardMethod::class)->add(OnAccount::class);

    // No eligibility closure registered: OnAccount::isAvailable() is false for
    // every cart, so it never enters availableFor()'s exclusive survivors.
    $session = CheckoutCart::session(CheckoutCart::orderable());

    $this->postJson(route('lunar.checkout.pay', $session->uuid), [
        'fingerprint' => CheckoutCart::fingerprint($session),
        'payment_method' => 'on-account',
    ])->assertStatus(422)
        ->assertJsonPath('errors.payment_method.0', 'The selected payment method is not available.');
});
