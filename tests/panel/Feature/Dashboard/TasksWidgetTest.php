<?php

use Lunar\Core\Models\Language;
use Lunar\Core\Models\ProductVariant;
use Lunar\Panel\Dashboard\DashboardRange;
use Lunar\Panel\Dashboard\Widgets\TasksWidget;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class)->group('cross-db');

beforeEach(fn () => Language::factory()->create(['default' => true]));

function outOfStockTaskCount(): int
{
    $tasks = collect(app(TasksWidget::class)->data(DashboardRange::ThirtyDays)['tasks']);

    return $tasks->firstWhere('key', 'out_of_stock')['count'];
}

it('counts stock-bound variants with nothing left to sell', function () {
    // Counted: stock-bound with no stock, and selling against incoming with none incoming.
    ProductVariant::factory()->stockBound()->create();
    ProductVariant::factory()->sellsAgainstIncoming()->create();

    // Not counted: stock to sell, incoming to sell against, or selling regardless.
    ProductVariant::factory()->stockBound()->inStock(3)->create();
    ProductVariant::factory()->sellsAgainstIncoming()->create(['stock_incoming' => 4]);
    ProductVariant::factory()->create();

    // Incoming is ignored when the variant does not sell against it.
    ProductVariant::factory()->stockBound()->create(['stock_incoming' => 4]);

    expect(outOfStockTaskCount())->toBe(3);
});
