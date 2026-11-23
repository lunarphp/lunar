<?php

use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Transaction;
use Lunar\Panel\Dashboard\DashboardRange;
use Lunar\Panel\Dashboard\OrderMetrics;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class)->group('cross-db');

beforeEach(function () {
    Carbon::setTestNow(CarbonImmutable::parse('2026-06-15 12:00:00'));
    Currency::factory()->create(['code' => 'GBP', 'default' => true, 'exchange_rate' => 1, 'decimal_places' => 2]);
    $this->metrics = app(OrderMetrics::class);
    $this->range = DashboardRange::ThirtyDays;
});

afterEach(fn () => Carbon::setTestNow());

/** A placed, non-cancelled order in the window unless overridden. */
function placedDashboardOrder(array $attributes = []): Order
{
    return Order::factory()->create(array_merge([
        'placed_at' => now()->subDays(2),
        'cancelled_at' => null,
        'exchange_rate' => 1,
        'new_customer' => false,
    ], $attributes));
}

it('sums order count and revenue in the window', function () {
    placedDashboardOrder(['total' => 10000, 'new_customer' => true, 'placed_at' => now()->subDay()]);
    placedDashboardOrder(['total' => 5000, 'placed_at' => now()->subDays(5)]);

    $totals = $this->metrics->totals($this->range->start(), $this->range->end());

    expect($totals->orders)->toBe(2)
        ->and($totals->revenue)->toBe(15000);
});

it('splits revenue and counts into new vs returning customers', function () {
    placedDashboardOrder(['total' => 10000, 'new_customer' => true]);
    placedDashboardOrder(['total' => 3000, 'new_customer' => false]);
    placedDashboardOrder(['total' => 2000, 'new_customer' => false]);

    $totals = $this->metrics->totals($this->range->start(), $this->range->end());

    expect($totals->newOrders)->toBe(1)
        ->and($totals->newRevenue)->toBe(10000)
        ->and($totals->repeatOrders)->toBe(2)
        ->and($totals->repeatRevenue)->toBe(5000);
});

it('values revenue in the default currency via the captured exchange rate', function () {
    placedDashboardOrder(['total' => 4000, 'exchange_rate' => 2]);

    $totals = $this->metrics->totals($this->range->start(), $this->range->end());

    expect($totals->revenue)->toBe(2000);
});

it('excludes unplaced, cancelled and out-of-window orders', function () {
    placedDashboardOrder(['total' => 1000]);
    placedDashboardOrder(['total' => 9999, 'placed_at' => null]);
    placedDashboardOrder(['total' => 9999, 'cancelled_at' => now()->subDay()]);
    placedDashboardOrder(['total' => 9999, 'placed_at' => now()->subDays(40)]);

    $totals = $this->metrics->totals($this->range->start(), $this->range->end());

    expect($totals->orders)->toBe(1)
        ->and($totals->revenue)->toBe(1000);
});

it('buckets the daily series and aligns totals to the window', function () {
    placedDashboardOrder(['total' => 10000, 'placed_at' => now()->subDay()]);
    placedDashboardOrder(['total' => 5000, 'placed_at' => now()->subDays(5)]);

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
    placedDashboardOrder(['total' => 8000, 'placed_at' => now()->startOfDay()->addHours(9)]);

    $series = app(OrderMetrics::class)->series(DashboardRange::Today);

    expect($series['revenue'])->toHaveCount(24)
        ->and($series['revenue'][9])->toBe(8000)
        ->and(array_sum($series['revenue']))->toBe(8000);
});

it('groups revenue by an order column', function () {
    $channelA = Channel::factory()->create();
    $channelB = Channel::factory()->create();

    placedDashboardOrder(['total' => 10000, 'channel_id' => $channelA->id]);
    placedDashboardOrder(['total' => 5000, 'channel_id' => $channelA->id]);
    placedDashboardOrder(['total' => 2000, 'channel_id' => $channelB->id]);

    $byChannel = $this->metrics->revenueByColumn($this->range->start(), $this->range->end(), 'channel_id');

    expect($byChannel[$channelA->id])->toBe(15000)
        ->and($byChannel[$channelB->id])->toBe(2000);
});

it('nets successful refunds out of revenue', function () {
    $refunded = placedDashboardOrder(['total' => 6000, 'payment_status' => 'refunded']);
    Transaction::factory()->for($refunded)->create(['type' => 'capture', 'amount' => 6000]);
    Transaction::factory()->for($refunded)->create(['type' => 'refund', 'amount' => 6000]);

    $partial = placedDashboardOrder(['total' => 10000, 'payment_status' => 'partially-refunded']);
    Transaction::factory()->for($partial)->create(['type' => 'capture', 'amount' => 10000]);
    Transaction::factory()->for($partial)->create(['type' => 'refund', 'amount' => 2500]);
    // A failed refund attempt moved no money, so it does not reduce revenue.
    Transaction::factory()->for($partial)->create(['type' => 'refund', 'amount' => 1000, 'success' => false]);

    // Refunds are in the order's currency, valued at its captured rate.
    $foreign = placedDashboardOrder(['total' => 4000, 'exchange_rate' => 2, 'payment_status' => 'partially-refunded']);
    Transaction::factory()->for($foreign)->create(['type' => 'capture', 'amount' => 4000]);
    Transaction::factory()->for($foreign)->create(['type' => 'refund', 'amount' => 1000]);

    $totals = $this->metrics->totals($this->range->start(), $this->range->end());
    $series = $this->metrics->series($this->range);
    $byChannel = $this->metrics->revenueByColumn($this->range->start(), $this->range->end(), 'channel_id');

    expect($totals->revenue)->toBe(7500 + 1500)
        ->and($totals->orders)->toBe(3)
        ->and(array_sum($series['revenue']))->toBe(9000)
        ->and($byChannel[$refunded->channel_id])->toBe(0)
        ->and($byChannel[$partial->channel_id])->toBe(7500)
        ->and($byChannel[$foreign->channel_id])->toBe(1500);
});

it('excludes orders whose payment was voided or failed', function () {
    placedDashboardOrder(['total' => 1000, 'payment_status' => 'paid']);

    $voided = placedDashboardOrder(['total' => 9999, 'payment_status' => 'voided']);
    Transaction::factory()->for($voided)->create(['type' => 'capture', 'amount' => 9999, 'success' => false]);

    $totals = $this->metrics->totals($this->range->start(), $this->range->end());
    $series = $this->metrics->series($this->range);
    $byChannel = $this->metrics->revenueByColumn($this->range->start(), $this->range->end(), 'channel_id');

    expect($totals->orders)->toBe(1)
        ->and($totals->revenue)->toBe(1000)
        ->and(array_sum($series['revenue']))->toBe(1000)
        ->and(array_sum($series['orders']))->toBe(1)
        ->and($byChannel->has($voided->channel_id))->toBeFalse();
});
