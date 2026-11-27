<?php

use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Order;
use Lunar\Filament\Widgets\Dashboard\Orders\OrdersSalesChart;
use Lunar\Filament\Widgets\Dashboard\Orders\OrderTotalsChart;
use Lunar\Tests\Admin\Unit\Filament\TestCase;

uses(TestCase::class)
    ->group('widgets.dashboard', 'cross-db');

/**
 * The monthly grouping relies on `db_date()` producing the same values on
 * every driver. PostgreSQL used to read the MySQL codes as `TO_CHAR` codes,
 * so every month came back as `6m` and the chart showed zero throughout.
 * SQLite reads `%M` as minutes, so grouping by month name split a month's
 * orders across rows. The times below fall on different minutes for that reason.
 */
it('totals orders by the month they were placed in', function () {
    $this->travelTo('2026-06-15 12:00:00');

    $currency = Currency::factory()->create(['default' => true, 'decimal_places' => 2]);

    foreach ([
        ['2026-03-04 09:15:00', 1000],
        ['2026-03-21 17:40:00', 500],
        ['2026-05-10 11:05:00', 2500],
    ] as [$placedAt, $subTotal]) {
        Order::factory()->create([
            'currency_code' => $currency->code,
            'placed_at' => $placedAt,
            'sub_total' => $subTotal,
        ]);
    }

    $data = (fn () => $this->getData())->call(new OrderTotalsChart);

    // The current period runs monthly from June 2025 to June 2026.
    expect($data['datasets'][0]['data'])->toBe([
        0, 0, 0, 0, 0, 0, 0, 0, 0,
        15.0, 0, 25.0, 0,
    ]);
});

it('labels and counts the orders and sales chart by month', function () {
    $this->travelTo('2026-06-15 12:00:00');

    $currency = Currency::factory()->create(['default' => true, 'decimal_places' => 2]);

    foreach ([
        ['2026-03-04 09:15:00', 1000],
        ['2026-03-21 17:40:00', 500],
        ['2026-05-10 11:05:00', 2500],
    ] as [$placedAt, $subTotal]) {
        Order::factory()->create([
            'currency_code' => $currency->code,
            'placed_at' => $placedAt,
            'sub_total' => $subTotal,
        ]);
    }

    $data = (fn () => $this->getData())->call(new OrdersSalesChart);

    expect($data['labels'])->toBe(['March 2026', 'May 2026'])
        ->and(array_map('intval', $data['datasets'][0]['data']))->toBe([2, 1])
        ->and($data['datasets'][1]['data'])->toBe([15.0, 25.0]);
});
