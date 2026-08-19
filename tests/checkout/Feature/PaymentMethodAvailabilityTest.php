<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Checkout\Contracts\PaymentMethodRegistry;
use Lunar\Checkout\PaymentMethods\AbstractPaymentMethod;
use Lunar\Checkout\PaymentMethods\Offline;
use Lunar\Checkout\States\CheckoutSession\Completed;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Order;
use Lunar\Tests\Checkout\TestCase;
use Lunar\Tests\Checkout\Utils\CheckoutCart;

uses(TestCase::class, RefreshDatabase::class);

/**
 * The minimum a gateway has to declare when it extends the base class.
 */
class MinimalMethod extends AbstractPaymentMethod
{
    public function handle(): string
    {
        return 'minimal';
    }

    public function label(): string
    {
        return 'Minimal';
    }

    public function driver(): string
    {
        return 'offline';
    }

    public function component(): string
    {
        return 'minimal-panel';
    }
}

/**
 * A method only offered for baskets being collected — the shape of
 * pay-on-collection or any other cart-conditional method.
 */
class CollectOnlyMethod extends Offline
{
    public function handle(): string
    {
        return 'on-collection';
    }

    public function label(): string
    {
        return 'Pay on collection';
    }

    public function isAvailable(Cart $cart): bool
    {
        return (bool) $cart->getShippingOption()?->collect;
    }
}

it('defaults to an available, intent-requiring, non-express method', function () {
    $method = new MinimalMethod;
    $cart = CheckoutCart::orderable();

    expect($method->requiresIntent())->toBeTrue()
        ->and($method->isAvailable($cart))->toBeTrue()
        ->and($method->supportsExpress())->toBeFalse()
        ->and($method->expressComponent())->toBeNull()
        ->and($method->config())->toBe([]);
});

it('ships an offline method that needs no gateway confirmation', function () {
    $method = new Offline;

    expect($method->handle())->toBe('offline')
        ->and($method->driver())->toBe('offline')
        ->and($method->requiresIntent())->toBeFalse();
});

it('filters the registry by what the cart can actually use', function () {
    $registry = app(PaymentMethodRegistry::class);
    $registry->add(MinimalMethod::class)->add(CollectOnlyMethod::class);

    $delivery = CheckoutCart::orderable();

    expect(collect($registry->availableFor($delivery))->map->handle()->all())
        ->toBe(['minimal']);
});

it('offers a collect-only method once the basket is being collected', function () {
    $registry = app(PaymentMethodRegistry::class);
    $registry->add(MinimalMethod::class)->add(CollectOnlyMethod::class);

    $collection = CheckoutCart::orderable(collect: true);

    expect(collect($registry->availableFor($collection))->map->handle()->all())
        ->toBe(['minimal', 'on-collection']);
});

it('projects only the available methods to the checkout page', function () {
    app(PaymentMethodRegistry::class)->add(MinimalMethod::class)->add(CollectOnlyMethod::class);

    $cart = CheckoutCart::orderable();
    $session = CheckoutCart::session($cart);

    $this->get(route('lunar.checkout.show', $session->uuid), ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertJsonCount(1, 'props.checkout.paymentMethods')
        ->assertJsonPath('props.checkout.paymentMethods.0.handle', 'minimal');
});

it('refuses to pay with a method the cart cannot use', function () {
    app(PaymentMethodRegistry::class)->add(CollectOnlyMethod::class);

    $cart = CheckoutCart::orderable();
    $session = CheckoutCart::session($cart);

    $this->postJson(route('lunar.checkout.pay', $session->uuid), [
        'fingerprint' => CheckoutCart::fingerprint($session),
        'payment_method' => 'on-collection',
    ])->assertStatus(422);

    expect(Order::query()->count())->toBe(0);
});

it('refuses to create an intent for a method the cart cannot use', function () {
    app(PaymentMethodRegistry::class)->add(CollectOnlyMethod::class);

    $cart = CheckoutCart::orderable();
    $session = CheckoutCart::session($cart);

    $this->postJson(route('lunar.checkout.payment-intent.store', $session->uuid), [
        'payment_method' => 'on-collection',
    ])->assertStatus(422);
});

it('places a collection order through the shipped offline method', function () {
    app(PaymentMethodRegistry::class)->add(CollectOnlyMethod::class);

    $cart = CheckoutCart::orderable(collect: true);
    $session = CheckoutCart::session($cart);

    $this->postJson(route('lunar.checkout.pay', $session->uuid), [
        'fingerprint' => CheckoutCart::fingerprint($session),
        'payment_method' => 'on-collection',
    ])->assertSuccessful();

    expect($session->refresh()->status)->toBeInstanceOf(Completed::class)
        ->and(Order::query()->whereNotNull('placed_at')->count())->toBe(1);
});
