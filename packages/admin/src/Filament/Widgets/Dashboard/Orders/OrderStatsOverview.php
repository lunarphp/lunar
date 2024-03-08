<?php

namespace Lunar\Admin\Filament\Widgets\Dashboard\Orders;

use Filament\Support\Facades\FilamentIcon;
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

        $current30Days = $this->getOrderQuery(
            from: $date->clone()->subDays(30),
            to: $date->clone(),
        );

        $previous30Days = $this->getOrderQuery(
            from: $date->clone()->subDays(60),
            to: $date->clone()->subDays(30),
        );

        $current7Days = $this->getOrderQuery(
            from: $date->clone()->subDays(7),
            to: $date->clone(),
        );

        $previous7Days = $this->getOrderQuery(
            from: $date->clone()->subDays(14),
            to: $date->clone()->subDays(7),
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
            $this->getStatCount($today, $yesterday, 'stat_one'),
            $this->getStatCount($current7Days, $previous7Days, 'stat_two'),
            $this->getStatCount($current30Days, $previous30Days, 'stat_three'),
            $this->getStatTotal($today, $yesterday, 'stat_four'),
            $this->getStatTotal($current7Days, $previous7Days, 'stat_five'),
            $this->getStatTotal($current30Days, $previous30Days, 'stat_six'),
        ];
    }

    protected function getStatTotal($currentDate, $previousDate, $reference): Stat
    {
        $currentSubTotal = $currentDate->select(
            DB::RAW('sum(sub_total) as sub_total')
        )->first()->sub_total;

        $previousSubTotal = $previousDate->select(
            DB::RAW('sum(sub_total) as sub_total')
        )->first()->sub_total;

        $percentage = $previousSubTotal->value ?
            round((($currentSubTotal->value - $previousSubTotal->value) / $previousSubTotal->value) * 100) :
            ($currentSubTotal->value ? 100 : 0);

        $increase = $percentage > 0;
        $neutral = $percentage === 0;
        $trend = $neutral ? 'neutral' : ($increase ? 'increase' : 'decrease');

        return Stat::make(
            label: __('lunarpanel::widgets.dashboard.orders.order_stats_overview.'.$reference.'.label'),
            value: $currentSubTotal->formatted,
        )->description(
            __('lunarpanel::widgets.dashboard.orders.order_stats_overview.'.$reference.'.'.$trend, [
                'percentage' => abs($percentage),
                'total' => $previousSubTotal->formatted,
            ])
        )->descriptionIcon(
            FilamentIcon::resolve(
                $trend == 'neutral' ? 'lunar::trending-neutral' : ($increase ? 'lunar::trending-up' : 'lunar::trending-down')
            )
        )
            ->color($trend == 'neutral' ? 'gray' : ($increase ? 'success' : 'danger'));
    }

    protected function getStatCount($currentDate, $previousDate, $reference): Stat
    {
        $currentCount = $currentDate->count();
        $previousCount = $previousDate->count();

        $percentage = $previousCount ?
            round((($currentCount - $previousCount) / $previousCount) * 100) :
            ($currentCount ? 100 : 0);

        $increase = $percentage > 0;
        $neutral = $percentage === 0;
        $trend = $neutral ? 'neutral' : ($increase ? 'increase' : 'decrease');

        $daysIncreased = $percentage > 0;

        return Stat::make(
            label: __('lunarpanel::widgets.dashboard.orders.order_stats_overview.'.$reference.'.label'),
            value: number_format($currentCount),
        )->description(
            __('lunarpanel::widgets.dashboard.orders.order_stats_overview.'.$reference.'.'.$trend, [
                'percentage' => abs($percentage),
                'count' => number_format($previousCount),
            ])
        )->descriptionIcon(
            FilamentIcon::resolve(
                $trend == 'neutral' ? '' : ($increase ? 'lunar::trending-up' : 'lunar::trending-down')
            )
        )
            ->color($trend == 'neutral' ? 'gray' : ($increase ? 'success' : 'danger'));
    }
}
