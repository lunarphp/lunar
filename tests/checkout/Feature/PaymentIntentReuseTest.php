<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Checkout\Contracts\PaymentMethodRegistry;
use Lunar\Checkout\Models\CheckoutSession;
use Lunar\Checkout\PaymentMethods\AbstractPaymentMethod;
use Lunar\Checkout\States\CheckoutSession\Expired;
use Lunar\Checkout\States\CheckoutSession\PaymentProcessing;
use Lunar\Core\Contracts\CreatesPaymentIntents;
use Lunar\Core\DataObjects\PaymentIntentDescriptor;
use Lunar\Core\Facades\CartSession;
use Lunar\Core\Facades\Payments;
use Lunar\Core\Models\Cart;
use Lunar\Core\PaymentTypes\OfflinePayment;
use Lunar\Tests\Checkout\TestCase;
use Lunar\Tests\Checkout\Utils\CheckoutCart;

uses(TestCase::class, RefreshDatabase::class);

/**
 * A cart-scoped gateway the way Stripe is: the same cart always resolves the
 * same intent reference, however many sessions come and go around it.
 */
class ReusedIntentGateway extends OfflinePayment implements CreatesPaymentIntents
{
    public function createIntent(Cart $cart): PaymentIntentDescriptor
    {
        return new PaymentIntentDescriptor('pi_reused_'.$cart->id, 'secret_'.$cart->id);
    }
}

class ReusedIntentMethod extends AbstractPaymentMethod
{
    public function handle(): string
    {
        return 'reused-card';
    }

    public function label(): string
    {
        return 'Card';
    }

    public function driver(): string
    {
        return 'reused-intents';
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

function registerReusedIntentGateway(): void
{
    Payments::extend('reused-intents', fn () => app(ReusedIntentGateway::class));
    app(PaymentMethodRegistry::class)->add(ReusedIntentMethod::class);
}

it('lets a successor session take over the intent reference a dead session still holds', function () {
    registerReusedIntentGateway();

    $cart = CheckoutCart::orderable();
    CartSession::use($cart);

    // A previous session for this cart died holding the cart's intent ref.
    $dead = CheckoutSession::factory()->create([
        'cart_reference' => (string) $cart->id,
        'status' => Expired::$name,
        'payment_intent_ref' => 'pi_reused_'.$cart->id,
        'expires_at' => now()->subHour(),
    ]);

    $session = CheckoutCart::session($cart);

    $this->postJson(route('lunar.checkout.payment-intent.store', $session->uuid), [
        'payment_method' => 'reused-card',
    ])->assertOk()->assertJsonPath('intent', 'pi_reused_'.$cart->id);

    expect($session->refresh()->payment_intent_ref)->toBe('pi_reused_'.$cart->id)
        ->and($dead->refresh()->payment_intent_ref)->toBeNull();
});

it('never robs the intent reference from a session pinned mid-payment', function () {
    registerReusedIntentGateway();

    $cart = CheckoutCart::orderable();
    CartSession::use($cart);

    // In practice a same-cart sibling cannot coexist with a pin (the
    // active_cart_reference unique index and the sibling_payment_processing
    // guard both forbid it), so the pinned holder is crafted against another
    // cart reference purely to exercise the relinquish guard: a pin must
    // never be robbed, whatever row holds it.
    $pinned = CheckoutSession::factory()->create([
        'status' => PaymentProcessing::$name,
        'payment_intent_ref' => 'pi_reused_'.$cart->id,
        'payment_processing_at' => now(),
    ]);

    $session = CheckoutCart::session($cart);

    $response = $this->postJson(route('lunar.checkout.payment-intent.store', $session->uuid), [
        'payment_method' => 'reused-card',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('errors.payment_method.0', 'Another payment attempt for this basket is still finishing. Wait a moment and try again.');

    expect($response->json('message'))->not->toContain('SQLSTATE')
        ->and($pinned->refresh()->payment_intent_ref)->toBe('pi_reused_'.$cart->id);
});
