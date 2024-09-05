<?php

namespace Lunar\Admin\Filament\Resources\CustomerResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;
use Lunar\DataTypes\Price;
use Lunar\Facades\DB;
use Lunar\Models\Currency;

class CustomerStatsOverviewWidget extends BaseWidget
{
    public ?Model $record = null;

    protected static string $view = 'filament-widgets::stats-overview-widget';

    protected function getStats(): array
    {
        if (! $this->record) {
            return [];
        }

        $avg = (int) round($this->record->orders()->average(
            DB::RAW('sub_total * exchange_rate')
        ));

        $total = (int) round($this->record->orders()->sum(
            DB::RAW('sub_total * exchange_rate')
        ));

        $totalSpend = new Price($total, Currency::modelClass()::getDefault());

        $avgSpend = new Price($avg, Currency::modelClass()::getDefault());

        return [
            Stat::make(__('lunarpanel::widgets.customer.stats_overview.total_orders.label'), $this->record->orders()->count()),
            Stat::make(__('lunarpanel::widgets.customer.stats_overview.avg_spend.label'), $avgSpend->formatted),
            Stat::make(__('lunarpanel::widgets.customer.stats_overview.total_spend.label'), $totalSpend->formatted),
        ];
    }
}
