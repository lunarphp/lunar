<?php

namespace Lunar\Filament\Widgets\Customer;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Currency;

class CustomerStatsOverviewWidget extends BaseWidget
{
    public ?Model $record = null;

    protected string $view = 'filament-widgets::stats-overview-widget';

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

        $currency = Currency::getDefault();

        $totalSpend = new PriceValue($total, $currency);
        $avgSpend = new PriceValue($avg, $currency);

        return [
            Stat::make(__('lunar-filament::widgets.customer.stats_overview.total_orders.label'), $this->record->orders()->count()),
            Stat::make(__('lunar-filament::widgets.customer.stats_overview.avg_spend.label'), $avgSpend->format()),
            Stat::make(__('lunar-filament::widgets.customer.stats_overview.total_spend.label'), $totalSpend->format()),
        ];
    }
}
