<?php

namespace Lunar\Admin\Filament\Pages;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Support\Facades\FilamentIcon;
use Lunar\Admin\Filament\Widgets\Dashboard\LatestOrders;
use Lunar\Admin\Filament\Widgets\Dashboard\SalesPerformance;
use Lunar\Admin\Filament\Widgets\Dashboard\StatsOverview;
use Lunar\Admin\Support\Pages\BaseDashboard;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?int $navigationSort = 1;

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        DatePicker::make('startDate'),
                        DatePicker::make('endDate'),
                        // ...
                    ])
                    ->columns(2),
            ]);
    }

    public function getWidgets(): array
    {
        return [
            StatsOverview::class,
            SalesPerformance::class,
            LatestOrders::class,
        ];
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::dashboard');
    }
}
