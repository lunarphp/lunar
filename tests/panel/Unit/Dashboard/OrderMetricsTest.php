<?php

use Lunar\Core\Models\Currency;
use Lunar\Panel\Dashboard\OrderMetrics;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

function metrics(): OrderMetrics
{
    return new OrderMetrics;
}

it('leaves amounts below the compact threshold fully formatted', function () {
    Currency::factory()->create(['code' => 'GBP', 'default' => true, 'exchange_rate' => 1]);

    // £9,999.99 (999999 minor) is under the £10,000 threshold.
    expect(metrics()->formatCompact(999999))->toBe('£9,999.99');
    expect(metrics()->formatCompact(0))->toBe('£0.00');
});

it('abbreviates thousands, millions and billions above the threshold', function () {
    Currency::factory()->create(['code' => 'GBP', 'default' => true, 'exchange_rate' => 1]);

    expect(metrics()->formatCompact(1_234_500))->toBe('£12.3k');      // £12,345.00
    expect(metrics()->formatCompact(139_863_513))->toBe('£1.4M');     // £1,398,635.13
    expect(metrics()->formatCompact(250_000_000_000))->toBe('£2.5B'); // £2,500,000,000
});

it('keeps the sign on negative (net-refund) totals', function () {
    Currency::factory()->create(['code' => 'GBP', 'default' => true, 'exchange_rate' => 1]);

    expect(metrics()->formatCompact(-139_863_513))->toBe('-£1.4M');
});
