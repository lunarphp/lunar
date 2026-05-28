<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Contracts\OrderStateConfig;
use Lunar\Core\Enums\OrderStateCategory;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\States\Order\DefaultOrderStateConfig;
use Lunar\Core\States\Order\Payment\Captured;
use Lunar\Core\States\Order\PaymentState;
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

class PartiallyCaptured extends PaymentState
{
    public static string $name = 'partially-captured';

    public function label(): string
    {
        return 'Partially Captured';
    }

    public function category(): OrderStateCategory
    {
        return OrderStateCategory::Active;
    }
}

class CustomOrderStateConfig extends DefaultOrderStateConfig
{
    public function paymentStates(): array
    {
        return [...parent::paymentStates(), PartiallyCaptured::class];
    }

    public function paymentTransitions(): array
    {
        return [
            ...parent::paymentTransitions(),
            Captured::class => [PartiallyCaptured::class, ...parent::paymentTransitions()[Captured::class]],
            PartiallyCaptured::class => [Captured::class],
        ];
    }
}

test('binding a custom OrderStateConfig adds a new payment state to the machine', function () {
    app()->bind(OrderStateConfig::class, CustomOrderStateConfig::class);
    flushSpatieStateMapping();

    $order = Order::factory()->create(['payment_status' => 'captured']);

    // The dev's new state is now an instantiable target — without changing
    // any core code or swapping the Order model.
    $order->payment_status->transitionTo(PartiallyCaptured::class);

    expect($order->fresh()->payment_status)->toBeInstanceOf(PartiallyCaptured::class);
});

test('a state from the custom catalog round-trips through the cast', function () {
    app()->bind(OrderStateConfig::class, CustomOrderStateConfig::class);
    flushSpatieStateMapping();

    $order = Order::factory()->create(['payment_status' => PartiallyCaptured::$name]);

    expect($order->fresh()->payment_status)->toBeInstanceOf(PartiallyCaptured::class)
        ->and((string) $order->fresh()->payment_status)->toBe('partially-captured');
});
