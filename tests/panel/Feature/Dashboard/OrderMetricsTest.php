<?php

use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Order;
use Lunar\Panel\Dashboard\DashboardRange;
use Lunar\Panel\Dashboard\OrderMetrics;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    Carbon::setTestNow(CarbonImmutable::parse('2026-06-15 12:00:00'));
    Currency::factory()->create(['code' => 'GBP', 'default' => true, 'exchange_rate' => 1, 'decimal_places' => 2]);
    $this->metrics = app(OrderMetrics::class);
    $this->range = DashboardRange::ThirtyDays;
});

afterEach(fn () => Carbon::setTestNow());

/** A placed, non-cancelled order in the window unless overridden. */
function placedOrder(array $attributes = []): Order
{
    return Order::factory()->create(array_merge([
        'placed_at' => now()->subDays(2),
        'cancelled_at' => null,
        'exchange_rate' => 1,
        'new_customer' => false,
    ], $attributes));
}

it('sums order count and revenue in the window', function () {
    placedOrder(['total' => 10000, 'new_customer' => true, 'placed_at' => now()->subDay()]);
    placedOrder(['total' => 5000, 'placed_at' => now()->subDays(5)]);

    $totals = $this->metrics->totals($this->range->start(), $this->range->end());

    expect($totals->orders)->toBe(2)
        ->and($totals->revenue)->toBe(15000);
});

it('splits revenue and counts into new vs returning customers', function () {
    placedOrder(['total' => 10000, 'new_customer' => true]);
    placedOrder(['total' => 3000, 'new_customer' => false]);
    placedOrder(['total' => 2000, 'new_customer' => false]);

    $totals = $this->metrics->totals($this->range->start(), $this->range->end());

    expect($totals->newOrders)->toBe(1)
        ->and($totals->newRevenue)->toBe(10000)
        ->and($totals->repeatOrders)->toBe(2)
        ->and($totals->repeatRevenue)->toBe(5000);
});

it('values revenue in the default currency via the captured exchange rate', function () {
    placedOrder(['total' => 4000, 'exchange_rate' => 2]);

    $totals = $this->metrics->totals($this->range->start(), $this->range->end());

    expect($totals->revenue)->toBe(2000);
});

it('excludes unplaced, cancelled and out-of-window orders', function () {
    placedOrder(['total' => 1000]);
    placedOrder(['total' => 9999, 'placed_at' => null]);
    placedOrder(['total' => 9999, 'cancelled_at' => now()->subDay()]);
    placedOrder(['total' => 9999, 'placed_at' => now()->subDays(40)]);

    $totals = $this->metrics->totals($this->range->start(), $this->range->end());

    expect($totals->orders)->toBe(1)
        ->and($totals->revenue)->toBe(1000);
});

it('buckets the daily series and aligns totals to the window', function () {
    placedOrder(['total' => 10000, 'placed_at' => now()->subDay()]);
    placedOrder(['total' => 5000, 'placed_at' => now()->subDays(5)]);

    $series = $this->metrics->series($this->range);

    expect($series['revenue'])->toHaveCount(30)
        ->and(array_sum($series['revenue']))->toBe(15000)
        ->and(array_sum($series['orders']))->toBe(2);

    // Day 30 (last bucket) is "today"; the window starts 30 days back.
    $buckets = $this->range->buckets();
    $yesterday = collect($buckets)->search(fn ($b) => $b['start']->isSameDay(now()->subDay()));
    expect($series['revenue'][$yesterday])->toBe(10000);
});

it('buckets an hourly series for the Today range', function () {
    placedOrder(['total' => 8000, 'placed_at' => now()->startOfDay()->addHours(9)]);

    $series = app(OrderMetrics::class)->series(DashboardRange::Today);

    expect($series['revenue'])->toHaveCount(24)
        ->and($series['revenue'][9])->toBe(8000)
        ->and(array_sum($series['revenue']))->toBe(8000);
});

it('groups revenue by an order column', function () {
    $channelA = Channel::factory()->create();
    $channelB = Channel::factory()->create();

    placedOrder(['total' => 10000, 'channel_id' => $channelA->id]);
    placedOrder(['total' => 5000, 'channel_id' => $channelA->id]);
    placedOrder(['total' => 2000, 'channel_id' => $channelB->id]);

    $byChannel = $this->metrics->revenueByColumn($this->range->start(), $this->range->end(), 'channel_id');

    expect($byChannel[$channelA->id])->toBe(15000)
        ->and($byChannel[$channelB->id])->toBe(2000);
});
