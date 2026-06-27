<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Transaction;
use Lunar\DemoData\Generators\CatalogueGenerator;
use Lunar\DemoData\Generators\CustomersGenerator;
use Lunar\DemoData\Generators\FoundationGenerator;
use Lunar\DemoData\Generators\OrdersGenerator;
use Lunar\DemoData\Support\DemoContext;
use Lunar\Tests\DemoData\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function generateStore(int $products = 6): DemoContext
{
    Storage::fake((string) config('lunar.demo-data.asset_disk', 'public'));
    Config::set('lunar.demo-data.scales.small.products', $products);

    $context = DemoContext::fromConfig('small');

    app(FoundationGenerator::class)->generate($context);
    app(CatalogueGenerator::class)->generate($context);
    app(CustomersGenerator::class)->generate($context);
    app(OrdersGenerator::class)->generate($context);

    return $context;
}

function paymentStatuses(): array
{
    return Order::all()->map(fn ($o) => (string) $o->payment_status)->unique()->values()->all();
}

function fulfilmentStatuses(): array
{
    return Order::all()->map(fn ($o) => (string) $o->fulfilment_status)->unique()->values()->all();
}

test('it covers every payment status', function () {
    generateStore();

    expect(paymentStatuses())->toContain(
        'pending', 'authorized', 'partially-paid', 'paid', 'partially-refunded', 'refunded', 'voided',
    );
});

test('it covers every fulfilment status', function () {
    generateStore();

    expect(fulfilmentStatuses())->toContain(
        'unfulfilled', 'partially-fulfilled', 'fulfilled', 'partially-returned', 'returned',
    );
});

test('it exercises all three fulfilment methods', function () {
    generateStore();

    expect(Fulfilment::query()->pluck('method')->unique()->values()->all())
        ->toContain('shipping', 'collection', 'digital');
});

test('it produces cancelled, closed and on-hold states', function () {
    generateStore();

    expect(Order::query()->whereNotNull('cancelled_at')->exists())->toBeTrue();
    expect(Order::query()->whereNotNull('closed_at')->exists())->toBeTrue();
    expect(Fulfilment::query()->whereNotNull('held_at')->exists())->toBeTrue();
});

test('orders are placed and tied to customers and lines', function () {
    generateStore();

    $order = Order::query()->where('reference', 'DEMO-PAID-SHIPPED')->first();

    expect($order->placed_at)->not->toBeNull();
    expect($order->customer_id)->not->toBeNull();
    expect($order->lines()->count())->toBe(2);
    expect($order->billingAddress())->not->toBeNull();
    expect((string) $order->payment_status)->toBe('paid');
    expect((string) $order->fulfilment_status)->toBe('fulfilled');
});

test('the digital order is provisioned without shipping', function () {
    generateStore();

    $order = Order::query()->where('reference', 'DEMO-PAID-DIGITAL')->first();
    $line = $order->lines()->first();

    expect($line->requires_shipping)->toBeFalse();
    expect($line->requires_fulfilment)->toBeTrue();
    expect((string) $order->fulfilment_status)->toBe('fulfilled');
    expect($order->fulfilments()->first()->method)->toBe('digital');
});

test('its transactions use a resolvable payment driver', function () {
    generateStore();

    $transaction = Transaction::query()->firstOrFail();

    // The admin order view resolves the driver by name, so it must be registered.
    expect($transaction->driver)->toBe('offline');
    expect($transaction->driver())->not->toBeNull();
});

test('it is idempotent', function () {
    $context = generateStore();
    $before = Order::count();

    app(OrdersGenerator::class)->generate($context);

    expect(Order::count())->toBe($before);
});
