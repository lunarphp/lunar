<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Contracts\Actions\Orders\CancelsOrder;
use Lunar\Core\Contracts\Actions\Orders\ClosesOrder;
use Lunar\Core\Contracts\Actions\Orders\ReopensOrder;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\Order;
use Lunar\Tests\Core\TestCase;
use Spatie\Activitylog\Models\Activity;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    activity()->enableLogging();
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true]);
    Location::factory()->default()->create();

    $this->order = Order::factory()->create();
});

function latestOrderActivity(Order $order): ?Activity
{
    return Activity::query()->where('subject_id', $order->id)->latest('id')->first();
}

it('logs a dedicated event when an order is closed and reopened', function () {
    app(ClosesOrder::class)->execute($this->order);
    expect(latestOrderActivity($this->order)->event)->toBe('order-closed');

    app(ReopensOrder::class)->execute($this->order->refresh());
    expect(latestOrderActivity($this->order)->event)->toBe('order-reopened');
});

it('logs a dedicated event with the reason when an order is cancelled', function () {
    app(CancelsOrder::class)->execute($this->order, 'items-unavailable', 'No stock');

    $log = Activity::query()->where('event', 'order-cancelled')->latest('id')->first();

    expect($log)->not->toBeNull()
        ->and($log->subject_id)->toBe($this->order->id)
        ->and($log->getExtraProperty('reason'))->toBe('items-unavailable');
});

it('does not log a generic "updated" entry when closing', function () {
    app(ClosesOrder::class)->execute($this->order);

    expect(Activity::query()->where('subject_id', $this->order->id)->where('event', 'updated')->exists())
        ->toBeFalse();
});
