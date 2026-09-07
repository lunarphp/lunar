<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Checkout\Contracts\Actions\SetsFulfilment;
use Lunar\Checkout\Contracts\CheckoutDriver;
use Lunar\Checkout\DataTypes\CollectionPoint;
use Lunar\Core\Facades\CartSession;
use Lunar\Core\Facades\ShippingManifest;
use Lunar\Core\Models\Order;
use Lunar\Tests\Checkout\TestCase;
use Lunar\Tests\Checkout\Utils\CheckoutCart;
use Lunar\Tests\Checkout\Utils\CollectionPointsStub;

uses(TestCase::class, RefreshDatabase::class);

afterEach(fn () => CollectionPointsStub::unbind());

it('stamps the chosen point onto the collect option and not onto couriers', function () {
    CollectionPointsStub::bind([new CollectionPoint('dartford', 'Dartford', ['DA2 6EP'])]);
    $cart = CheckoutCart::orderable(collect: true);
    $cart = app(SetsFulfilment::class)->execute($cart, 'collect');

    $options = ShippingManifest::getOptions($cart);
    $collect = $options->firstWhere('collect', true);

    expect($collect->meta['collection_point'])->toBe([
        'handle' => 'dartford',
        'name' => 'Dartford',
        'lines' => ['DA2 6EP'],
        'meta' => [],
    ]);

    $options->where('collect', false)->each(fn ($option) => expect($option->meta['collection_point'] ?? null)->toBeNull());
});

it('leaves option meta alone when no point is chosen', function () {
    $cart = CheckoutCart::orderable(collect: true);

    $collect = ShippingManifest::getOptions($cart)->firstWhere('collect', true);

    expect($collect->meta['collection_point'] ?? null)->toBeNull();
});

it('lands on the placed order shipping line and in the order meta', function () {
    CollectionPointsStub::bind([new CollectionPoint('dartford', 'Dartford', ['DA2 6EP'])]);
    $cart = CheckoutCart::orderable(collect: true);
    $cart = app(SetsFulfilment::class)->execute($cart, 'collect');
    CartSession::use($cart);

    $driver = app(CheckoutDriver::class);
    $session = $driver->resolveOrCreateSession($cart);
    $driver->complete($session, $session->cart_fingerprint);

    $order = Order::query()->firstOrFail();
    $line = $order->shippingLines()->firstOrFail();

    expect($line->meta['collect'])->toBeTrue()
        ->and($line->meta['collection_point']['handle'])->toBe('dartford')
        ->and($order->meta['collection_point']['handle'])->toBe('dartford')
        ->and($order->meta['fulfilment'])->toBe('collect');
});
