<?php

namespace Lunar\Admin\Filament\Widgets\Dashboard;

use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Lunar\DataTypes\Price;
use Lunar\Facades\DB;
use Lunar\Models\Currency;
use Lunar\Models\Order;
use Lunar\Models\Product;

class StatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        $from = now()->parse(
            $this->filters['startDate'] ?? now()->subDays(15)->startOfDay()
        );

        $to = now()->parse(
            $this->filters['endDate'] ?? now()->endOfDay()
        );

        return [
            Stat::make(
                label: __('lunarpanel::widgets.dashboard.stats_overview.stat_one.label'),
                value: $this->getNewProductStat($from, $to),
            ),
            Stat::make(
                label: __('lunarpanel::widgets.dashboard.stats_overview.stat_two.label'),
                value: $this->getReturningCustomersPercentStat($from, $to),
            ),
            Stat::make(
                label: __('lunarpanel::widgets.dashboard.stats_overview.stat_three.label'),
                value: $this->getTurnoverStat($from, $to)->formatted(),
            ),
            Stat::make(
                label: __('lunarpanel::widgets.dashboard.stats_overview.stat_four.label'),
                value: $this->getOrdersCountStat($from, $to),
            ),
        ];
    }

    protected function getNewProductStat($from, $to): string
    {
        return number_format(
            Product::whereBetween('created_at', [
                $from,
                $to,
            ])->count(), 0);
    }

    public function getReturningCustomersPercentStat($from, $to)
    {
        $orders = Order::select(
            DB::RAW('COUNT(*) as count'),
            'new_customer'
        )->whereBetween('created_at', [
            $from,
            $to,
        ])->groupBy('new_customer')->get();

        if ($orders->isEmpty()) {
            return 0;
        }

        $returning = $orders->first(fn ($order) => ! $order->new_customer);
        $new = $orders->first(fn ($order) => $order->new_customer);

        if (! $returning || ! $returning->count) {
            return 0;
        }

        if (! $new || ! $new->count) {
            return 100;
        }

        return round(($returning->count / ($new->count + $returning->count)) * 100, 2);
    }

    protected function getOrdersCountStat($from, $to)
    {
        return number_format(Order::whereBetween('placed_at', [
            $from,
            $to,
        ])->count(), 0);
    }

    protected function getTurnoverStat($from, $to)
    {
        $query = Order::whereBetween('placed_at', [
            $from,
            $to,
        ])->select(
            DB::RAW('SUM(sub_total) as total')
        )->first();

        return new Price($query->total->value, Currency::getDefault(), 1);
    }
}
