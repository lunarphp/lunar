<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Checkout\Contracts\ExplainsUnavailability;
use Lunar\Checkout\Contracts\PaymentMethodRegistry;
use Lunar\Checkout\Express;
use Lunar\Checkout\PaymentMethods\AbstractPaymentMethod;
use Lunar\Core\Contracts\CreatesPaymentIntents;
use Lunar\Core\Contracts\SupportsPaymentHolds;
use Lunar\Core\DataObjects\HoldDescription;
use Lunar\Core\DataObjects\PaymentIntentDescriptor;
use Lunar\Core\Enums\HoldAdjustment;
use Lunar\Core\Facades\CartSession;
use Lunar\Core\Facades\Payments;
use Lunar\Core\Models\Cart;
use Lunar\Core\PaymentTypes\OfflinePayment;
use Lunar\Tests\Checkout\TestCase;
use Lunar\Tests\Checkout\Utils\CheckoutCart;

uses(TestCase::class, RefreshDatabase::class);

/**
 * A hold-capable gateway registered under its own driver key, kept separate
 * from PaymentHoldTest's FakeHoldGateway so the two files' Payments::extend()
 * registrations never collide.
 */
class FakeExpressHoldGateway extends OfflinePayment implements CreatesPaymentIntents, SupportsPaymentHolds
{
    public function createHold(Cart $cart): PaymentIntentDescriptor
    {
        return new PaymentIntentDescriptor('hold_fake_'.$cart->id);
    }

    public function createIntent(Cart $cart): PaymentIntentDescriptor
    {
        return new PaymentIntentDescriptor('pi_fake_'.$cart->id);
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
}

class FakeExpressMethod extends AbstractPaymentMethod
{
    public function handle(): string
    {
        return 'fake-express';
    }

    public function label(): string
    {
        return 'Fake wallet';
    }

    public function driver(): string
    {
        return 'fake-express-hold';
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

class FakeCardOnlyMethod extends AbstractPaymentMethod
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

    public function component(): string
    {
        return 'stripe-card';
    }
}

/**
 * Declares express support but its registered driver ("offline") has no
 * hold capability: the misconfiguration both Express::projection() and the
 * checkout's own paymentMethods projection must refuse rather than trust the
 * method's own claim.
 */
class FakeExpressNoHoldMethod extends AbstractPaymentMethod
{
    public function handle(): string
    {
        return 'fake-express-no-hold';
    }

    public function label(): string
    {
        return 'Fake wallet (no hold)';
    }

    public function driver(): string
    {
        return 'offline';
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
 * Declares express support against a driver key nothing has registered: the
 * shape of a gateway package removed or misconfigured after the method was
 * registered. Payments::driver() throws InvalidArgumentException for this;
 * neither projection may let that surface as a 500.
 */
class FakeExpressUnregisteredDriverMethod extends AbstractPaymentMethod
{
    public function handle(): string
    {
        return 'fake-express-unregistered';
    }

    public function label(): string
    {
        return 'Fake wallet (unregistered driver)';
    }

    public function driver(): string
    {
        return 'does-not-exist';
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

function registerFakeExpressHoldGateway(): void
{
    Payments::extend('fake-express-hold', fn () => app(FakeExpressHoldGateway::class));
}

it('projects only express-eligible available methods', function () {
    registerFakeExpressHoldGateway();
    app(PaymentMethodRegistry::class)->add(FakeExpressMethod::class)->add(FakeCardOnlyMethod::class);

    $cart = CheckoutCart::orderable();

    $projection = Express::projection($cart);

    expect($projection['methods'])->toHaveCount(1)
        ->and($projection['methods'][0]['handle'])->toBe('fake-express')
        ->and($projection['methods'][0]['expressComponent'])->toBe('fake-express');
});

it('reports whether the basket is payable at all and why not', function () {
    registerFakeExpressHoldGateway();
    app(PaymentMethodRegistry::class)->add(FakeExpressMethod::class);

    $projection = Express::projection(CheckoutCart::orderable());

    expect($projection['payable'])->toBeTrue()
        ->and($projection['unavailable'])->toBe([]);

    app()->forgetInstance(PaymentMethodRegistry::class);
    app(PaymentMethodRegistry::class)->add(new class extends FakeExpressMethod implements ExplainsUnavailability
    {
        public function isAvailable(Cart $cart): bool
        {
            return false;
        }

        public function unavailableReason(Cart $cart): ?string
        {
            return 'Too small.';
        }
    });

    $projection = Express::projection(CheckoutCart::orderable());

    expect($projection['methods'])->toBe([])
        ->and($projection['payable'])->toBeFalse()
        ->and($projection['unavailable'])->toBe(['Too small.']);
});

it('excludes an express method whose driver cannot actually hold', function () {
    app(PaymentMethodRegistry::class)->add(FakeExpressNoHoldMethod::class);

    $cart = CheckoutCart::orderable();

    expect(Express::projection($cart)['methods'])->toBeEmpty();
});

it('never 500s projecting an express method registered against an unknown driver', function () {
    app(PaymentMethodRegistry::class)->add(FakeExpressUnregisteredDriverMethod::class);

    $cart = CheckoutCart::orderable();

    expect(Express::projection($cart)['methods'])->toBeEmpty();
});

it('enforces supportsExpress server-side against the driver capability on the checkout page', function () {
    app(PaymentMethodRegistry::class)->add(FakeExpressNoHoldMethod::class);

    $cart = CheckoutCart::orderable();
    $session = CheckoutCart::session($cart);

    $this->get(route('lunar.checkout.show', $session->uuid), ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertJsonPath('props.checkout.paymentMethods.0.handle', 'fake-express-no-hold')
        ->assertJsonPath('props.checkout.paymentMethods.0.supportsExpress', false);
});

it('answers the start action with session urls for JSON clients', function () {
    $cart = CheckoutCart::orderable();
    CartSession::use($cart);

    $this->postJson(route('lunar.checkout.start'))
        ->assertOk()
        ->assertJsonStructure(['uuid', 'urls' => ['quote', 'paymentIntent', 'confirm', 'contact']]);
});
