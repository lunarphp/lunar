<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Checkout\Contracts\ExclusivePaymentMethod;
use Lunar\Checkout\Contracts\PaymentMethodRegistry;
use Lunar\Checkout\Express;
use Lunar\Checkout\PaymentMethods\AbstractPaymentMethod;
use Lunar\Checkout\PaymentMethods\Offline;
use Lunar\Core\Models\Cart;
use Lunar\Tests\Checkout\TestCase;
use Lunar\Tests\Checkout\Utils\CheckoutCart;

uses(TestCase::class, RefreshDatabase::class);

class PlainCardMethod extends AbstractPaymentMethod
{
    public function handle(): string
    {
        return 'plain-card';
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
        return 'plain-card';
    }
}

class ExclusiveTermsMethod extends Offline implements ExclusivePaymentMethod
{
    public static bool $available = true;

    public function handle(): string
    {
        return 'terms';
    }

    public function isAvailable(Cart $cart): bool
    {
        return static::$available;
    }
}

class SecondExclusiveMethod extends ExclusiveTermsMethod
{
    public function handle(): string
    {
        return 'terms-two';
    }
}

beforeEach(function () {
    ExclusiveTermsMethod::$available = true;
});

function handlesFor(Cart $cart): array
{
    return array_map(fn ($m) => $m->handle(), app(PaymentMethodRegistry::class)->availableFor($cart));
}

it('offers only the exclusive method when it is available', function () {
    app(PaymentMethodRegistry::class)->add(PlainCardMethod::class)->add(ExclusiveTermsMethod::class);

    expect(handlesFor(CheckoutCart::orderable()))->toBe(['terms']);
});

it('hides nothing when the exclusive method is unavailable', function () {
    ExclusiveTermsMethod::$available = false;
    app(PaymentMethodRegistry::class)->add(PlainCardMethod::class)->add(ExclusiveTermsMethod::class);

    expect(handlesFor(CheckoutCart::orderable()))->toBe(['plain-card']);
});

it('returns every available exclusive method in registration order', function () {
    app(PaymentMethodRegistry::class)->add(SecondExclusiveMethod::class)->add(PlainCardMethod::class)->add(ExclusiveTermsMethod::class);

    expect(handlesFor(CheckoutCart::orderable()))->toBe(['terms-two', 'terms']);
});

it('projects no express wallets when an exclusive method is available', function () {
    app(PaymentMethodRegistry::class)->add(PlainCardMethod::class)->add(ExclusiveTermsMethod::class);

    expect(Express::projection(CheckoutCart::orderable())['methods'])->toBe([]);
});
