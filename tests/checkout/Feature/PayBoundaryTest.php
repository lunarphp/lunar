<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Checkout\Contracts\CheckoutDriver;
use Lunar\Checkout\Contracts\PaymentMethod;
use Lunar\Checkout\Contracts\PaymentMethodRegistry;
use Lunar\Checkout\Models\CheckoutSession;
use Lunar\Checkout\States\CheckoutSession\Completed;
use Lunar\Checkout\States\CheckoutSession\PaymentProcessing;
use Lunar\Core\Contracts\StorefrontSession;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\DataTypes\ShippingOption;
use Lunar\Core\Facades\CartSession;
use Lunar\Core\Facades\ShippingManifest;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\TaxClass;
use Lunar\Core\Models\TaxZone;
use Lunar\Tests\Checkout\TestCase;

uses(TestCase::class, RefreshDatabase::class);

/**
 * A payment method that needs no gateway confirmation — the shape of an
 * offline / pay-on-collection / invoice-terms method (spec 0002 §A).
 */
class SynchronousTestMethod implements PaymentMethod
{
    public function handle(): string
    {
        return 'on-collection';
    }

    public function label(): string
    {
        return 'Pay on collection';
    }

    public function driver(): string
    {
        return 'offline';
    }

    public function requiresIntent(): bool
    {
        return false;
    }

    public function component(): string
    {
        return 'offline-notice';
    }

    public function config(): array
    {
        return [];
    }

    public function supportsExpress(): bool
    {
        return false;
    }

    public function expressComponent(): ?string
    {
        return null;
    }
}

/**
 * A gateway-backed method: the async path that pins for confirmation.
 */
class IntentTestMethod extends SynchronousTestMethod
{
    public function handle(): string
    {
        return 'card';
    }

    public function requiresIntent(): bool
    {
        return true;
    }
}

/**
 * An orderable cart with one paid-for line, plus the storefront context a
 * real request carries.
 */
function payBoundaryCart(int $unitPrice = 1000): Cart
{
    Language::factory()->create(['code' => 'en', 'default' => true]);
    CustomerGroup::factory()->create(['default' => true]);
    TaxZone::factory()->create(['default' => true]);

    $channel = Channel::factory()->create(['handle' => 'webstore', 'default' => true]);
    $currency = Currency::factory()->create(['code' => 'GBP', 'default' => true, 'decimal_places' => 2]);
    $taxClass = TaxClass::factory()->create(['default' => true]);
    $country = Country::factory()->create(['iso2' => 'GB']);

    app(StorefrontSession::class)->setChannel($channel)->setCurrency($currency);

    $cart = Cart::factory()->create([
        'channel_id' => $channel->id,
        'currency_id' => $currency->id,
    ]);

    $variant = ProductVariant::factory()->create([
        'tax_class_id' => $taxClass->id,
        'unit_quantity' => 1,
    ]);

    Price::factory()->create([
        'price' => $unitPrice,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $variant->getMorphClass(),
        'purchasable_id' => $variant->id,
        'quantity' => 1,
    ]);

    foreach (['shipping', 'billing'] as $type) {
        $cart->addresses()->create([
            'type' => $type,
            'country_id' => $country->id,
            'first_name' => 'Terry',
            'last_name' => 'Sparks',
            'line_one' => '1 Trade Counter Way',
            'city' => 'London',
            'postcode' => 'SE1 1AA',
        ]);
    }

    CartSession::use($cart);

    // An orderable cart needs a shipping option applied; the shipping package
    // isn't loaded here, so put one on the manifest directly.
    $option = new ShippingOption(
        name: 'Standard delivery',
        description: 'Standard delivery',
        identifier: 'standard',
        price: new PriceValue(0, $currency),
        taxClass: $taxClass,
    );

    ShippingManifest::addOption($option);
    $cart->refresh()->calculate()->setShippingOption($option);

    return $cart->refresh()->calculate();
}

function payBoundarySession(Cart $cart): CheckoutSession
{
    return app(CheckoutDriver::class)->createSession($cart);
}

function payBoundaryFingerprint(CheckoutSession $session): string
{
    return app(CheckoutDriver::class)->fingerprint($session);
}

it('completes the session in place for a method that needs no intent', function () {
    app(PaymentMethodRegistry::class)->add(SynchronousTestMethod::class);

    $cart = payBoundaryCart();
    $session = payBoundarySession($cart);

    $this->postJson(route('lunar.checkout.pay', $session->uuid), [
        'fingerprint' => payBoundaryFingerprint($session),
        'payment_method' => 'on-collection',
    ])->assertSuccessful();

    $session->refresh();

    expect($session->status)->toBeInstanceOf(Completed::class)
        ->and($session->order_reference)->not->toBeNull()
        ->and(Order::query()->count())->toBe(1);
});

it('still pins for confirmation when the method needs an intent', function () {
    app(PaymentMethodRegistry::class)->add(IntentTestMethod::class);

    $cart = payBoundaryCart();
    $session = payBoundarySession($cart);

    $this->postJson(route('lunar.checkout.pay', $session->uuid), [
        'fingerprint' => payBoundaryFingerprint($session),
        'payment_method' => 'card',
    ])->assertSuccessful();

    $session->refresh();

    expect($session->status)->toBeInstanceOf(PaymentProcessing::class)
        ->and(Order::query()->count())->toBe(0);
});

it('completes a zero-total cart synchronously even for an intent method', function () {
    app(PaymentMethodRegistry::class)->add(IntentTestMethod::class);

    // Nothing to charge — spec 0002 §A: a zero total forces the synchronous
    // path regardless of the method's declared capability.
    $cart = payBoundaryCart(unitPrice: 0);
    $session = payBoundarySession($cart);

    $this->postJson(route('lunar.checkout.pay', $session->uuid), [
        'fingerprint' => payBoundaryFingerprint($session),
        'payment_method' => 'card',
    ])->assertSuccessful();

    expect($session->refresh()->status)->toBeInstanceOf(Completed::class);
});

it('refuses to complete synchronously on a stale fingerprint', function () {
    app(PaymentMethodRegistry::class)->add(SynchronousTestMethod::class);

    $cart = payBoundaryCart();
    $session = payBoundarySession($cart);

    $this->postJson(route('lunar.checkout.pay', $session->uuid), [
        'fingerprint' => 'not-the-current-state',
        'payment_method' => 'on-collection',
    ])->assertStatus(422);

    expect($session->refresh()->status)->not->toBeInstanceOf(Completed::class)
        ->and(Order::query()->count())->toBe(0);
});

it('rejects a payment method that is not registered', function () {
    $cart = payBoundaryCart();
    $session = payBoundarySession($cart);

    $this->postJson(route('lunar.checkout.pay', $session->uuid), [
        'fingerprint' => payBoundaryFingerprint($session),
        'payment_method' => 'carrier-pigeon',
    ])->assertStatus(422);
});

it('creates only one order when a synchronous pay is submitted twice', function () {
    app(PaymentMethodRegistry::class)->add(SynchronousTestMethod::class);

    $cart = payBoundaryCart();
    $session = payBoundarySession($cart);
    $fingerprint = payBoundaryFingerprint($session);

    $this->postJson(route('lunar.checkout.pay', $session->uuid), [
        'fingerprint' => $fingerprint,
        'payment_method' => 'on-collection',
    ])->assertSuccessful();

    $this->postJson(route('lunar.checkout.pay', $session->uuid), [
        'fingerprint' => $fingerprint,
        'payment_method' => 'on-collection',
    ])->assertSuccessful();

    expect(Order::query()->count())->toBe(1);
});
