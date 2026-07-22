<?php

namespace Lunar\Panel\Sections;

use Lunar\Panel\Dashboard\Widgets\ChannelsWidget;
use Lunar\Panel\Dashboard\Widgets\CustomerGroupsWidget;
use Lunar\Panel\Dashboard\Widgets\KpisWidget;
use Lunar\Panel\Dashboard\Widgets\LowStockWidget;
use Lunar\Panel\Dashboard\Widgets\NewVsRepeatWidget;
use Lunar\Panel\Dashboard\Widgets\RecentOrdersWidget;
use Lunar\Panel\Dashboard\Widgets\RevenueChartWidget;
use Lunar\Panel\Dashboard\Widgets\TasksWidget;
use Lunar\Panel\Dashboard\Widgets\TopProductsWidget;

/**
 * The first-party dashboard widgets, registered through the public section
 * API exactly as an add-on would register its own. The Dashboard page and
 * navigation item are seeded by the panel itself.
 */
class DashboardSection extends Section
{
    public function key(): string
    {
        return 'dashboard';
    }

    public function widgets(): array
    {
        return [
            KpisWidget::class,
            RevenueChartWidget::class,
            RecentOrdersWidget::class,
            TopProductsWidget::class,
            ChannelsWidget::class,
            NewVsRepeatWidget::class,
            CustomerGroupsWidget::class,
            LowStockWidget::class,
            TasksWidget::class,
        ];
    }
}
