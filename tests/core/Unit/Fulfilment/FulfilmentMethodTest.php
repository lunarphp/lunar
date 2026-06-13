<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Contracts\Actions\Orders\ResolvesFulfilmentStatus;
use Lunar\Core\Drivers\FulfilmentMethods\Collection as CollectionMethod;
use Lunar\Core\Drivers\FulfilmentMethods\Digital;
use Lunar\Core\Drivers\FulfilmentMethods\Shipping;
use Lunar\Core\Enums\FulfilmentStateCategory;
use Lunar\Core\Exceptions\FulfilmentException;
use Lunar\Core\Facades\FulfilmentMethods;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\States\Fulfilment\Collected;
use Lunar\Core\States\Fulfilment\Pending;
use Lunar\Core\States\Fulfilment\Provisioned;
use Lunar\Core\States\Fulfilment\ReadyForCollection;
use Lunar\Core\States\Fulfilment\Returned;
use Lunar\Core\States\Fulfilment\Shipped;
use Lunar\Core\States\Order\Fulfilment\Fulfilled;
use Lunar\Core\States\Order\Fulfilment\Unfulfilled;
use Lunar\Tests\Core\TestCase;
use Spatie\ModelStates\Exceptions\CouldNotPerformTransition;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true]);
});

function resolveStatus(Order $order): string
{
    return app(ResolvesFulfilmentStatus::class)->execute($order);
}

// ---------------------------------------------------------------- the manifest

test('the manifest registers the three core methods in priority order', function () {
    expect(FulfilmentMethods::all()->keys()->all())->toBe(['digital', 'collection', 'shipping'])
        ->and(FulfilmentMethods::get('shipping'))->toBeInstanceOf(Shipping::class)
        ->and(FulfilmentMethods::get('collection'))->toBeInstanceOf(CollectionMethod::class)
        ->and(FulfilmentMethods::get('digital'))->toBeInstanceOf(Digital::class)
        ->and(FulfilmentMethods::get('nope'))->toBeNull();
});

test('the manifest groups state names by category across every method', function () {
    expect(FulfilmentMethods::stateNamesIn(FulfilmentStateCategory::Fulfilled))
        ->toContain('shipped', 'collected', 'provisioned')
        ->and(FulfilmentMethods::stateNamesIn(FulfilmentStateCategory::Returned))
        ->toBe(['returned'])
        ->and(FulfilmentMethods::stateNamesIn(FulfilmentStateCategory::Outstanding))
        ->toContain('pending', 'in-progress', 'ready-for-collection');
});

// ------------------------------------------------------------ method assignment

test('a mixed basket is split into one parcel per claiming method', function () {
    $order = Order::factory()->create(['placed_at' => null]);
    $physical = OrderLine::factory()->create([
        'order_id' => $order->id, 'type' => 'physical', 'quantity' => 1,
    ]);
    $digital = OrderLine::factory()->create([
        'order_id' => $order->id, 'type' => 'digital',
        'requires_shipping' => false, 'requires_fulfilment' => true, 'quantity' => 1,
    ]);

    $order->update(['placed_at' => now()]);

    $byMethod = $order->fulfilments()->get()->keyBy('method');

    expect($order->fulfilments()->count())->toBe(2)
        ->and($byMethod['shipping']->lines()->pluck('order_line_id')->all())->toBe([$physical->id])
        ->and($byMethod['digital']->lines()->pluck('order_line_id')->all())->toBe([$digital->id]);
});

test('a collection shipping option routes physical lines to the collection method', function () {
    $order = Order::factory()->create(['placed_at' => null]);
    $physical = OrderLine::factory()->create([
        'order_id' => $order->id, 'type' => 'physical', 'quantity' => 2,
    ]);
    // The chosen shipping option was a collection — stamped onto the line.
    OrderLine::factory()->create([
        'order_id' => $order->id, 'type' => 'shipping',
        'requires_shipping' => false, 'requires_fulfilment' => false,
        'meta' => ['collect' => true],
    ]);

    $order->update(['placed_at' => now()]);

    $fulfilment = $order->fulfilments()->sole();

    expect($fulfilment->method)->toBe('collection')
        ->and($fulfilment->state)->toBeInstanceOf(Pending::class)
        ->and($fulfilment->lines()->pluck('order_line_id')->all())->toBe([$physical->id]);
});

test('a plain shipping option keeps physical lines on the shipping method', function () {
    $order = Order::factory()->create(['placed_at' => null]);
    OrderLine::factory()->create(['order_id' => $order->id, 'type' => 'physical', 'quantity' => 1]);
    OrderLine::factory()->create([
        'order_id' => $order->id, 'type' => 'shipping',
        'requires_shipping' => false, 'requires_fulfilment' => false,
        'meta' => ['collect' => false],
    ]);

    $order->update(['placed_at' => now()]);

    expect($order->fulfilments()->sole()->method)->toBe('shipping');
});

// ---------------------------------------------------------- per-method graphs

test('a collection parcel runs pending to ready-for-collection to collected', function () {
    $fulfilment = Fulfilment::factory()->collection()->create(['state' => 'pending']);

    $fulfilment->state->transitionTo(ReadyForCollection::class);
    expect((string) $fulfilment->fresh()->state)->toBe('ready-for-collection');

    $fulfilment->refresh()->state->transitionTo(Collected::class);
    expect((string) $fulfilment->fresh()->state)->toBe('collected');
});

test('a collection parcel cannot be shipped (a shipping-only state)', function () {
    $fulfilment = Fulfilment::factory()->collection()->create(['state' => 'pending']);

    expect(fn () => $fulfilment->state->transitionTo(Shipped::class))
        ->toThrow(CouldNotPerformTransition::class);
});

test('a digital parcel runs pending to provisioned and cannot be returned', function () {
    $fulfilment = Fulfilment::factory()->digital()->create(['state' => 'pending']);

    $fulfilment->state->transitionTo(Provisioned::class);
    expect((string) $fulfilment->fresh()->state)->toBe('provisioned');

    expect(fn () => $fulfilment->refresh()->state->transitionTo(Returned::class))
        ->toThrow(CouldNotPerformTransition::class);
});

test('transitionableStates is filtered to the parcel method', function () {
    $shipping = Fulfilment::factory()->create(['state' => 'pending']);
    $collection = Fulfilment::factory()->collection()->create(['state' => 'pending']);

    $shippingTargets = collect($shipping->state->transitionableStates());
    $collectionTargets = collect($collection->state->transitionableStates());

    expect($shippingTargets)->toContain('shipped')->not->toContain('collected')
        ->and($collectionTargets)->toContain('collected')->not->toContain('shipped');
});

// --------------------------------------------------------------------- verbs

test('fulfil advances a collection parcel to collected and stamps the timestamp', function () {
    $fulfilment = Fulfilment::factory()->collection()->create(['state' => 'pending']);

    $fulfilment->fulfil();

    expect((string) $fulfilment->fresh()->state)->toBe('collected')
        ->and($fulfilment->fresh()->shipped_at)->not->toBeNull();
});

test('fulfil advances a digital parcel to provisioned', function () {
    $fulfilment = Fulfilment::factory()->digital()->create(['state' => 'pending']);

    $fulfilment->fulfil();

    expect((string) $fulfilment->fresh()->state)->toBe('provisioned')
        ->and($fulfilment->fresh()->shipped_at)->not->toBeNull();
});

test('ship is rejected on a method that carries no tracking', function () {
    $collection = Fulfilment::factory()->collection()->create(['state' => 'pending']);
    $digital = Fulfilment::factory()->digital()->create(['state' => 'pending']);

    expect(fn () => $collection->ship(['tracking_number' => 'X']))->toThrow(FulfilmentException::class)
        ->and(fn () => $digital->ship())->toThrow(FulfilmentException::class)
        ->and((string) $collection->fresh()->state)->toBe('pending');
});

test('a shipping parcel still ships with tracking and stamps the timestamp', function () {
    $fulfilment = Fulfilment::factory()->create(['state' => 'pending']);

    $fulfilment->ship(['tracking_number' => 'AB123']);

    expect((string) $fulfilment->fresh()->state)->toBe('shipped')
        ->and($fulfilment->fresh()->shipped_at)->not->toBeNull()
        ->and($fulfilment->trackings()->count())->toBe(1);
});

// ----------------------------------------------------------- cross-method guards

test('parcels of different methods cannot be merged', function () {
    $order = Order::factory()->create();

    $target = Fulfilment::factory()->create(['order_id' => $order->id, 'state' => 'pending']);
    Fulfilment::factory()->collection()->create([
        'order_id' => $order->id,
        'location_id' => $target->location_id,
        'state' => 'pending',
    ]);

    $sources = Fulfilment::query()->where('order_id', $order->id)->where('method', 'collection')->get();

    expect(fn () => $target->merge($sources))->toThrow(FulfilmentException::class);
});

// --------------------------------------------------------------------- rollup

test('a provisionable digital order is unfulfilled until the parcel is provisioned', function () {
    $order = Order::factory()->create();
    $line = OrderLine::factory()->create([
        'order_id' => $order->id, 'type' => 'digital',
        'requires_shipping' => false, 'requires_fulfilment' => true, 'quantity' => 1,
    ]);

    expect(resolveStatus($order))->toBe(Unfulfilled::class);

    $fulfilment = Fulfilment::factory()->provisioned()->create(['order_id' => $order->id]);
    $fulfilment->lines()->createQuietly(['order_line_id' => $line->id, 'quantity' => 1]);

    expect(resolveStatus($order))->toBe(Fulfilled::class);
});

test('a service line that needs no fulfilment stays out of the rollup', function () {
    $order = Order::factory()->create();
    OrderLine::factory()->create([
        'order_id' => $order->id, 'type' => 'service',
        'requires_shipping' => false, 'requires_fulfilment' => false, 'quantity' => 1,
    ]);

    expect(resolveStatus($order))->toBe(Fulfilled::class);
});
