<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Contracts\OrderStateConfig;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\States\Order\DefaultOrderStateConfig;
use Lunar\Core\States\Order\Order\AwaitingPayment;
use Lunar\Core\States\Order\Order\Cancelled;
use Lunar\Core\States\Order\Order\InProcess;
use Lunar\Core\States\Order\OrderState;
use Lunar\Tests\Core\TestCase;
use Spatie\ModelStates\State;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true]);
});

/**
 * Spatie's State base class caches the resolved state mapping in a private
 * static array, populated the first time any code calls
 * `BaseState::getStateMapping()`. In production this is fine — bindings are
 * set during service-provider registration before any model uses the cast,
 * so the cache fills with the host's catalogue. In tests that swap the
 * binding mid-run, we have to flush.
 */
function flushSpatieStateMapping(): void
{
    $reflection = new ReflectionClass(State::class);
    $property = $reflection->getProperty('stateMapping');
    $property->setAccessible(true);
    $property->setValue(null, []);
}

class AwaitingStock extends OrderState
{
    public static string $name = 'awaiting-stock';

    public function label(): string
    {
        return 'Awaiting Stock';
    }
}

class CustomOrderStateConfig extends DefaultOrderStateConfig
{
    public function orderStates(): array
    {
        return [...parent::orderStates(), AwaitingStock::class];
    }

    public function orderTransitions(): array
    {
        return [
            ...parent::orderTransitions(),
            AwaitingPayment::class => [AwaitingStock::class, ...parent::orderTransitions()[AwaitingPayment::class]],
            AwaitingStock::class => [InProcess::class, Cancelled::class],
        ];
    }
}

test('binding a custom OrderStateConfig adds a new order state to the machine', function () {
    app()->bind(OrderStateConfig::class, CustomOrderStateConfig::class);
    flushSpatieStateMapping();

    $order = Order::factory()->create(['status' => 'awaiting-payment']);

    // The dev's new state is now an instantiable transition target — without
    // changing any core code or swapping the Order model.
    $order->status->transitionTo(AwaitingStock::class);

    expect($order->fresh()->status)->toBeInstanceOf(AwaitingStock::class);
});

test('a state from the custom catalog round-trips through the cast', function () {
    app()->bind(OrderStateConfig::class, CustomOrderStateConfig::class);
    flushSpatieStateMapping();

    $order = Order::factory()->create(['status' => AwaitingStock::$name]);

    expect($order->fresh()->status)->toBeInstanceOf(AwaitingStock::class)
        ->and((string) $order->fresh()->status)->toBe('awaiting-stock');
});
