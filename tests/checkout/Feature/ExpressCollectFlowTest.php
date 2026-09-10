<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Checkout\Contracts\Actions\SetsFulfilment;
use Lunar\Checkout\Contracts\PaymentMethodRegistry;
use Lunar\Checkout\DataTypes\PickupPoint;
use Lunar\Checkout\Models\CheckoutSession;
use Lunar\Checkout\PaymentMethods\AbstractPaymentMethod;
use Lunar\Checkout\States\CheckoutSession\Completed;
use Lunar\Core\Contracts\CreatesPaymentIntents;
use Lunar\Core\Contracts\SupportsPaymentHolds;
use Lunar\Core\Contracts\SupportsPaymentIntents;
use Lunar\Core\DataObjects\HoldDescription;
use Lunar\Core\DataObjects\PaymentIntentDescriptor;
use Lunar\Core\Enums\HoldAdjustment;
use Lunar\Core\Enums\PaymentIntentStatus;
use Lunar\Core\Facades\CartSession;
use Lunar\Core\Facades\Payments;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Order;
use Lunar\Core\PaymentTypes\OfflinePayment;
use Lunar\Tests\Checkout\TestCase;
use Lunar\Tests\Checkout\Utils\CheckoutCart;
use Lunar\Tests\Checkout\Utils\PickupPointsStub;

uses(TestCase::class, RefreshDatabase::class);

afterEach(fn () => PickupPointsStub::unbind());

/**
 * Host-mode express collect (spec 0013 §F, spec 0012 §F/§G): the wallet is
 * mounted on the basket page, so the cart already carries the mode and the
 * branch, the sheet asks for no shipping address, and the only address the
 * cart gets is the wallet's billing address. Its own gateway and method
 * classes so nothing here collides with the other express suites'.
 */
class CollectExpressGateway extends OfflinePayment implements CreatesPaymentIntents, SupportsPaymentHolds, SupportsPaymentIntents
{
    public function createHold(Cart $cart): PaymentIntentDescriptor
    {
        return new PaymentIntentDescriptor('hold_collect_'.$cart->id);
    }

    public function createIntent(Cart $cart): PaymentIntentDescriptor
    {
        return new PaymentIntentDescriptor('pi_collect_'.$cart->id);
    }

    public function describeHold(string $reference): ?HoldDescription
    {
        return null;
    }

    public function adjustHold(string $reference, int $amountMinor): HoldAdjustment
    {
        return HoldAdjustment::Ok;
    }

    public function captureHold(string $reference, int $amountMinor): void {}

    public function fetchIntent(string $reference): PaymentIntentStatus
    {
        return PaymentIntentStatus::RequiresCapture;
    }

    public function voidIntent(string $reference): void {}

    public function refundIntent(string $reference, int $amountMinor, string $idempotencyKey): string
    {
        return 'refund_'.$reference;
    }
}

class CollectExpressMethod extends AbstractPaymentMethod
{
    public function handle(): string
    {
        return 'collect-wallet';
    }

    public function label(): string
    {
        return 'Collect wallet';
    }

    public function driver(): string
    {
        return 'collect-express-hold';
    }

    public function requiresIntent(): bool
    {
        return true;
    }

    public function component(): string
    {
        return 'fake-wallet';
    }

    public function supportsExpress(): bool
    {
        return true;
    }

    public function expressComponent(): ?string
    {
        return 'fake-express';
    }
}

/**
 * A guest basket that chose collect and a branch before any address existed:
 * the state the cart page's own fulfilment endpoint leaves behind.
 */
function collectBasket(): Cart
{
    Payments::extend('collect-express-hold', fn () => app(CollectExpressGateway::class));
    app(PaymentMethodRegistry::class)->add(CollectExpressMethod::class);

    PickupPointsStub::bind([
        new PickupPoint('london', 'London', ['Camberwell']),
        new PickupPoint('dartford', 'Dartford', ['DA2 6EP']),
    ]);

    $cart = CheckoutCart::orderable(collect: true);
    $cart->addresses()->delete();
    $cart = $cart->refresh();

    app(SetsFulfilment::class)->execute($cart, 'collect', 'dartford');

    $cart = $cart->refresh();
    CartSession::use($cart);

    return $cart;
}

it('places an express collect order from the basket page wallet', function () {
    $cart = collectBasket();

    // 1. The sheet's first interaction mints the session; its JSON answer is
    //    what tells the wallet the cart collects (spec 0013 §E).
    $start = $this->postJson(route('lunar.checkout.start'))->assertOk();

    expect($start->json('fulfilment'))->toBe('collect')
        ->and($start->json('pickupPointId'))->toBe('dartford')
        ->and($start->json('pickupPoints'))->toHaveCount(2);

    $uuid = $start->json('uuid');

    // 2. The wallet shares the payer's email first (emailRequired on the
    //    sheet), which is what lets the pay boundary reach the customer.
    $this->post($start->json('urls.contact'), ['email' => 'terry@example.com'])->assertRedirect();

    //    Collect mode writes no shipping address and no shipping rate: the
    //    wallet's billing address is the only one, mirrored server-side onto
    //    the shipping row so the collect option can be stored.
    $this->postJson($start->json('urls.billingAddress'), [
        'first_name' => 'Terry',
        'last_name' => 'Sparks',
        'line1' => '4 Wallet Road',
        'city' => 'London',
        'postcode' => 'SE1 1AA',
        'country_code' => 'GB',
    ])->assertOk();

    // 3. The hold, minted against the cart the writes left behind.
    $this->postJson($start->json('urls.paymentIntent'), [
        'payment_method' => 'collect-wallet',
        'mode' => 'hold',
    ])->assertOk();

    // 4. Confirm & pay. Without the mirror above the cart has no address and
    //    no collect option, and the boundary refuses it as not orderable.
    $session = CheckoutSession::query()->where('uuid', $uuid)->firstOrFail();

    expect($session->payment_intent_ref)->toBe('hold_collect_'.$cart->id);

    $this->postJson(route('lunar.checkout.pay', $uuid), [
        'payment_method' => 'collect-wallet',
        'fingerprint' => CheckoutCart::fingerprint($session),
    ])->assertOk()
        ->assertJsonPath('pinned', true);

    // 5. The processing route captures the pinned hold and completes.
    $this->get(route('lunar.checkout.processing', $uuid));

    $order = Order::query()->firstOrFail();
    $line = $order->shippingLines()->firstOrFail();

    expect($session->refresh()->status)->toBeInstanceOf(Completed::class)
        ->and($line->meta['collect'])->toBeTrue()
        ->and($line->meta['pickup_point']['handle'])->toBe('dartford')
        ->and($order->meta['fulfilment'])->toBe('collect');
});
