<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Checkout\Contracts\PaymentMethodRegistry;
use Lunar\Checkout\Models\CheckoutSession;
use Lunar\Checkout\PaymentMethods\AbstractPaymentMethod;
use Lunar\Core\Contracts\CreatesPaymentIntents;
use Lunar\Core\DataObjects\PaymentIntentDescriptor;
use Lunar\Core\Facades\CartSession;
use Lunar\Core\Facades\Payments;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Customer;
use Lunar\Core\PaymentTypes\OfflinePayment;
use Lunar\Tests\Checkout\TestCase;
use Lunar\Tests\Checkout\Utils\CheckoutCart;
use Lunar\Tests\Core\Stubs\User;

uses(TestCase::class, RefreshDatabase::class);

/**
 * The structured error contract (code / reason / action) flashed on every
 * redirect OUT of the checkout, plus the guards that keep an empty basket
 * from ever rendering as a payable checkout.
 */
it('refuses to start a checkout for an empty cart', function () {
    CheckoutCart::orderable(); // storefront context (channel/currency)

    $empty = Cart::factory()->create([
        'channel_id' => Channel::query()->value('id'),
        'currency_id' => Currency::query()->value('id'),
    ]);
    CartSession::use($empty);

    $response = $this->from('/basket')->post(route('lunar.checkout.start'));

    $response->assertRedirect('/basket')
        ->assertSessionHas('lunar.checkout.error.code', 'cart_empty')
        ->assertSessionHas('lunar.checkout.error.action', 'view_basket');
});

it('bounces a session whose cart has been emptied back to the basket', function () {
    $cart = CheckoutCart::orderable();
    CartSession::use($cart);
    $session = CheckoutCart::session($cart);

    $cart->lines()->delete();

    $this->get(route('lunar.checkout.show', $session->uuid))
        ->assertRedirect('/')
        ->assertSessionHas('lunar.checkout.error.code', 'cart_empty');
});

it('flashes the structured expiry error when a session has died', function () {
    $cart = CheckoutCart::orderable();
    CartSession::use($cart);
    $session = CheckoutCart::session($cart);

    $session->update(['expires_at' => now()->subHour(), 'cancel_url' => 'https://store.test/basket']);

    $response = $this->get(route('lunar.checkout.show', $session->uuid));

    $response->assertRedirect('https://store.test/basket')
        ->assertSessionHas('lunar.checkout.error.code', 'session_expired')
        ->assertSessionHas('lunar.checkout.error.action', 'restart_checkout');

    expect(session('lunar.checkout.error.reason'))->toContain('expired');
});

it('does not mint a session for the empty cart a signed-in refresh resolves to', function () {
    // The real-world repro: order completed, the customer's live cart is a new
    // EMPTY one, and they refresh the (now expired) old checkout URL.
    $cart = CheckoutCart::orderable();
    CartSession::use($cart);
    $session = CheckoutCart::session($cart);

    $user = User::factory()->create();
    $customer = Customer::factory()->create();
    $user->customers()->attach($customer);
    $session->update(['customer_reference' => (string) $customer->id]);

    $emptyLiveCart = Cart::factory()->create([
        'channel_id' => $cart->channel_id,
        'currency_id' => $cart->currency_id,
    ]);
    CartSession::use($emptyLiveCart);

    $sessionsBefore = CheckoutSession::query()->count();

    $this->actingAs($user)
        ->get(route('lunar.checkout.show', $session->uuid))
        ->assertRedirect('/')
        ->assertSessionHas('lunar.checkout.error.code', 'cart_empty');

    expect(CheckoutSession::query()->count())->toBe($sessionsBefore);
});

/**
 * A gateway whose intent creation always fails, the way Stripe refuses a
 * below-minimum charge.
 */
class FailingIntentGateway extends OfflinePayment implements CreatesPaymentIntents
{
    public function createIntent(Cart $cart): PaymentIntentDescriptor
    {
        throw new RuntimeException('The amount must be greater than or equal to the minimum charge amount. See https://gateway.docs/internal');
    }
}

class FailingIntentMethod extends AbstractPaymentMethod
{
    public function handle(): string
    {
        return 'failing-card';
    }

    public function label(): string
    {
        return 'Card';
    }

    public function driver(): string
    {
        return 'failing-intents';
    }

    public function requiresIntent(): bool
    {
        return true;
    }

    public function component(): string
    {
        return 'stripe-card';
    }
}

it('never surfaces a raw gateway error from intent creation', function () {
    Payments::extend('failing-intents', fn () => app(FailingIntentGateway::class));
    app(PaymentMethodRegistry::class)->add(FailingIntentMethod::class);

    $cart = CheckoutCart::orderable();
    CartSession::use($cart);
    $session = CheckoutCart::session($cart);

    $response = $this->postJson(route('lunar.checkout.payment-intent.store', $session->uuid), [
        'payment_method' => 'failing-card',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('errors.payment_method.0', 'Card payments are unavailable right now. Please try again in a moment.');

    expect($response->json('message'))->not->toContain('minimum charge');
});
