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
use Lunar\Admin\Support\Concerns\CallsHooks;
use Lunar\Admin\Support\Pages\BaseDashboard;

class Dashboard extends BaseDashboard
{
    use CallsHooks;

    protected static ?int $navigationSort = 1;

    public function getWidgets(): array
    {
        return self::callLunarHook('getWidgets', $this->getDefaultWidgets());
    }

    public function getDefaultWidgets(): array
    {
        return [
            ...$this->getDefaultOverviewWidgets(),
            ...$this->getDefaultChartsWidgets(),
            ...$this->getDefaultTableWidgets(),
        ];
    }

    public function getDefaultOverviewWidgets(): array
    {
        return self::callLunarHook('getOverviewWidgets', [
            OrderStatsOverview::class,
        ]);
    }

    public function getDefaultChartsWidgets(): array
    {
        return self::callLunarHook('getChartWidgets', [
            OrderTotalsChart::class,
            OrdersSalesChart::class,
            AverageOrderValueChart::class,
            NewVsReturningCustomersChart::class,
        ]);
    }

    public function getDefaultTableWidgets(): array
    {
        return self::callLunarHook('getTableWidgets', [
            PopularProductsTable::class,
            LatestOrdersTable::class,
        ]);
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::dashboard');
    }
}
