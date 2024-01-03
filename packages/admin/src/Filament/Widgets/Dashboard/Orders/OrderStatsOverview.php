<?php

namespace Lunar\Admin\Filament\Widgets\Dashboard\Orders;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Lunar\Facades\DB;
use Lunar\Models\Order;

class OrderStatsOverview extends BaseWidget
{
    protected function getOrderQuery(\DateTime $from = null, \DateTime $to = null)
    {
        return Order::whereNotNull('placed_at')
            ->whereBetween('placed_at', [
                $from,
                $to,
            ]);
    }

    protected function getStats(): array
    {
        $date = now()->settings([
            'monthOverflow' => false,
        ]);

        $currentMonth = $this->getOrderQuery(
            from: $date->clone()->startOfMonth(),
            to: $date->clone(),
        );

        $previousMonth = $this->getOrderQuery(
            from: $date->clone()->subMonth()->startOfMonth(),
            to: $date->clone(),
        );

        $currentWeek = $this->getOrderQuery(
            from: $date->clone()->startOfWeek(),
            to: $date->clone(),
        );

        $previousWeek = $this->getOrderQuery(
            from: $date->clone()->subWeek()->startOfWeek(),
            to: $date->clone()->subWeek(),
        );

        $today = $this->getOrderQuery(
            from: $date->clone()->startOfDay(),
            to: $date->clone(),
        );

        $yesterday = $this->getOrderQuery(
            from: $date->clone()->subDay()->startOfDay(),
            to: $date->clone()->subDay(),
        );

        return [
            Stat::make(
                label: __('lunarpanel::widgets.dashboard.orders.order_stats_overview.stat_one.label'),
                value: number_format($today->count()),
            )->description(
                __('lunarpanel::widgets.dashboard.orders.order_stats_overview.stat_one.description', [
                    'count' => number_format($yesterday->count()),
                ])
            ),
            Stat::make(
                label: __('lunarpanel::widgets.dashboard.orders.order_stats_overview.stat_two.label'),
                value: number_format($currentWeek->count()),
            )->description(
                __('lunarpanel::widgets.dashboard.orders.order_stats_overview.stat_two.description', [
                    'count' => number_format($previousWeek->count()),
                ])
            ),
            Stat::make(
                label: __('lunarpanel::widgets.dashboard.orders.order_stats_overview.stat_three.label'),
                value: number_format($currentMonth->count()),
            )->description(
                __('lunarpanel::widgets.dashboard.orders.order_stats_overview.stat_three.description', [
                    'count' => number_format($previousMonth->count()),
                ])
            ),
            Stat::make(
                label: __('lunarpanel::widgets.dashboard.orders.order_stats_overview.stat_four.label'),
                value: $today->select(
                    DB::RAW('sum(sub_total) as sub_total')
                )->first()->sub_total->formatted,
            )->description(
                __('lunarpanel::widgets.dashboard.orders.order_stats_overview.stat_four.description', [
                    'total' => $yesterday->select(
                        DB::RAW('sum(sub_total) as sub_total')
                    )->first()->sub_total->formatted,
                ])
            ),
            Stat::make(
                label: __('lunarpanel::widgets.dashboard.orders.order_stats_overview.stat_five.label'),
                value: $currentWeek->select(
                    DB::RAW('sum(sub_total) as sub_total')
                )->first()->sub_total->formatted,
            )->description(
                __('lunarpanel::widgets.dashboard.orders.order_stats_overview.stat_five.description', [
                    'total' => $previousWeek->select(
                        DB::RAW('sum(sub_total) as sub_total')
                    )->first()->sub_total->formatted,
                ])
            ),
            Stat::make(
                label: __('lunarpanel::widgets.dashboard.orders.order_stats_overview.stat_six.label'),
                value: $currentMonth->select(
                    DB::RAW('sum(sub_total) as sub_total')
                )->first()->sub_total->formatted,
            )->description(
                __('lunarpanel::widgets.dashboard.orders.order_stats_overview.stat_six.description', [
                    'total' => $previousMonth->select(
                        DB::RAW('sum(sub_total) as sub_total')
                    )->first()->sub_total->formatted,
                ])
            ),

        ];
    }
}
