<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\DemoData\Database\Seeders\DemoDataSeeder;
use Lunar\DemoData\Support\DemoContext;
use Lunar\Tests\DemoData\TestCase;

use function Pest\Laravel\artisan;

uses(TestCase::class, RefreshDatabase::class);

function seedStore(bool $fresh = false): void
{
    Storage::fake((string) config('lunar.demo-data.asset_disk', 'public'));
    Config::set('lunar.demo-data.scales.small.products', 6);

    app(DemoDataSeeder::class)
        ->usingContext(DemoContext::fromConfig('small', $fresh))
        ->run();
}

test('a small seed produces every derived state and all three methods', function () {
    seedStore();

    expect(Order::all()->map(fn ($o) => (string) $o->payment_status)->unique()->values()->all())
        ->toContain('pending', 'authorized', 'partially-paid', 'paid', 'partially-refunded', 'refunded', 'voided');

    expect(Order::all()->map(fn ($o) => (string) $o->fulfilment_status)->unique()->values()->all())
        ->toContain('unfulfilled', 'partially-fulfilled', 'fulfilled', 'partially-returned', 'returned');

    expect(Fulfilment::query()->pluck('method')->unique()->values()->all())
        ->toContain('shipping', 'collection', 'digital');
});

test('--fresh wipes and rebuilds without duplicating', function () {
    seedStore();

    $orders = Order::count();
    $variants = ProductVariant::count();

    seedStore(fresh: true);

    expect(Order::count())->toBe($orders);
    expect(ProductVariant::count())->toBe($variants);
});

test('seeding reproduces the same store structure across runs', function () {
    seedStore(fresh: true);
    $firstProducts = Product::all()->map(fn ($p) => $p->translate('name'))->sort()->values()->all();
    $firstOrders = Order::query()->orderBy('reference')->pluck('reference')->all();
    $firstCounts = [Order::count(), Customer::count(), ProductVariant::count()];

    seedStore(fresh: true);

    expect(Product::all()->map(fn ($p) => $p->translate('name'))->sort()->values()->all())->toBe($firstProducts);
    expect(Order::query()->orderBy('reference')->pluck('reference')->all())->toBe($firstOrders);
    expect([Order::count(), Customer::count(), ProductVariant::count()])->toBe($firstCounts);
});

test('the command refuses to reseed without --fresh', function () {
    Storage::fake((string) config('lunar.demo-data.asset_disk', 'public'));
    Config::set('lunar.demo-data.scales.small.products', 3);

    artisan('lunar:demo-data')->assertExitCode(0);

    artisan('lunar:demo-data')
        ->expectsOutputToContain('already present')
        ->assertExitCode(0);

    artisan('lunar:demo-data', ['--fresh' => true])
        ->expectsOutputToContain('Demo data seeded.')
        ->assertExitCode(0);
});
