<?php

namespace Lunar\Admin\Filament\Pages;

use Filament\Support\Facades\FilamentIcon;
use Lunar\Admin\Filament\Widgets\Dashboard\Orders\AverageOrderValueChart;
use Lunar\Admin\Filament\Widgets\Dashboard\Orders\LatestOrdersTable;
use Lunar\Admin\Filament\Widgets\Dashboard\Orders\NewVsReturningCustomersChart;
use Lunar\Admin\Filament\Widgets\Dashboard\Orders\OrdersSalesChart;
use Lunar\Admin\Filament\Widgets\Dashboard\Orders\OrderStatsOverview;
use Lunar\Admin\Filament\Widgets\Dashboard\Orders\OrderTotalsChart;
use Lunar\Admin\Filament\Widgets\Dashboard\Orders\PopularProductsTable;
use Lunar\Admin\Support\Pages\BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?int $navigationSort = 1;

    public function getWidgets(): array
    {
        return [
            OrderStatsOverview::class,
            OrderTotalsChart::class,
            OrdersSalesChart::class,
            AverageOrderValueChart::class,
            NewVsReturningCustomersChart::class,
            PopularProductsTable::class,
            LatestOrdersTable::class,
        ];
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::dashboard');
    }
}
