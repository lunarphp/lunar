<?php

namespace Lunar\Admin\Filament\Widgets\Dashboard;

use Carbon\CarbonPeriod;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Lunar\Facades\DB;
use Lunar\Models\Currency;
use Lunar\Models\Order;

class SalesPerformance extends ApexChartWidget
{
    use InteractsWithPageFilters;

    /**
     * Chart Id
     */
    protected static string $chartId = 'salesPerformanceChart';

    protected function getHeading(): ?string
    {
        return __('lunarpanel::widgets.dashboard.sales_performance.heading');
    }

    protected function getOptions(): array
    {
        $currency = Currency::getDefault();

        $start = now()->parse(
            $this->filters['startDate'] ?? now()->subDays(15)->startOfDay()
        );

        $end = now()->parse(
            $this->filters['endDate'] ?? now()->endOfDay()
        );

        $thisPeriod = Order::select(
            DB::RAW('SUM(sub_total) as sub_total'),
            DB::RAW(db_date('placed_at', '%Y-%d', 'format_date'))
        )->whereNotNull('placed_at')
            ->whereBetween('placed_at', [
                $start,
                $end,
            ])->groupBy('format_date')->get();

        $previousPeriod = Order::select(
            DB::RAW('SUM(sub_total) as sub_total'),
            DB::RAW(db_date('placed_at', '%Y-%d', 'format_date'))
        )->whereNotNull('placed_at')
            ->whereBetween('placed_at', [
                $start->clone()->subYear(),
                $end->clone()->subYear(),
            ])->groupBy('format_date')->get();

        $period = CarbonPeriod::create($start, '1 day', $end);

        $thisPeriodDays = collect();
        $previousPeriodDays = collect();
        $days = collect();

        foreach ($period as $datetime) {
            $days->push($datetime->toDateTimeString());

            $dateFormat = 'Y-d';

            // Do we have some totals for this month?
            if ($totals = $thisPeriod->first(fn ($p) => $p->format_date == $datetime->format($dateFormat))) {
                $thisPeriodDays->push($totals->sub_total->decimal);
            } else {
                $thisPeriodDays->push(0);
            }
            if ($prevTotals = $previousPeriod->first(fn ($p) => $p->format_date == $datetime->clone()->subYear()->format($dateFormat))) {
                $previousPeriodDays->push($prevTotals->sub_total->decimal);
            } else {
                $previousPeriodDays->push(0);
            }

        }

        return [
            'chart' => [
                'type' => 'area',
                'toolbar' => [
                    'show' => false,
                ],
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'fill' => [
                'type' => 'gradient',
                'gradient' => [
                    'shadeIntensity' => 1,
                    'opacityFrom' => 0.45,
                    'opacityTo' => 0.05,
                    'stops' => [50, 100, 100, 100],
                ],
            ],
            'series' => [
                [
                    'name' => __('lunarpanel::widgets.dashboard.sales_performance.chart.series_one.label'),
                    'data' => $thisPeriodDays->toArray(),
                ],
                [
                    'name' => __('lunarpanel::widgets.dashboard.sales_performance.chart.series_two.label'),
                    'data' => $previousPeriodDays->toArray(),
                ],
            ],
            'xaxis' => [
                'type' => 'datetime',
                'categories' => $days->toArray(),
            ],
            'yaxis' => [
                'title' => [
                    'text' => __('lunarpanel::widgets.dashboard.sales_performance.chart.yaxis.label', [
                        'currency' => $currency->code,
                    ]),
                ],
            ],
            'tooltip' => [
                'x' => [
                    'format' => 'dd MMM yyyy',
                ],
            ],
        ];
    }
}
