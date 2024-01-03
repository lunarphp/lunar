<?php

namespace Lunar\Admin\Filament\Pages;

use Filament\Support\Facades\FilamentIcon;
use Lunar\Admin\Filament\Widgets\Dashboard\Orders\AverageOrderValueChart;
use Lunar\Admin\Filament\Widgets\Dashboard\Orders\NewVsReturningCustomersChart;
use Lunar\Admin\Filament\Widgets\Dashboard\Orders\OrdersSalesChart;
use Lunar\Admin\Filament\Widgets\Dashboard\Orders\OrderStatsOverview;
use Lunar\Admin\Filament\Widgets\Dashboard\Orders\OrderTotalsChart;
use Lunar\Admin\Support\Pages\BaseDashboard;

class Dashboard extends BaseDashboard
{
    //    use HasFiltersForm;

    protected static ?int $navigationSort = 1;

    //    public function filtersForm(Form $form): Form
    //    {
    //        return $form
    //            ->schema([
    //                Section::make()
    //                    ->schema([
    //                        DatePicker::make('startDate'),
    //                        DatePicker::make('endDate'),
    //                        // ...
    //                    ])
    //                    ->columns(2),
    //            ]);
    //    }

    public function getWidgets(): array
    {
        return [
            OrderStatsOverview::class,
            OrderTotalsChart::class,
            OrdersSalesChart::class,
            AverageOrderValueChart::class,
            NewVsReturningCustomersChart::class,
        ];
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::dashboard');
    }
}
