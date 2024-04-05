<?php

namespace Lunar\Admin\Filament\Widgets\Dashboard\Orders;

use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Lunar\Facades\DB;
use Lunar\Models\Currency;
use Lunar\Models\Order;

class OrdersSalesChart extends ApexChartWidget
{
    use InteractsWithPageFilters;

    /**
     * Chart Id
     */
    protected static string $chartId = 'ordersSalesChart';

    protected static ?string $pollingInterval = '60s';

    protected function getHeading(): ?string
    {
        return __('lunarpanel::widgets.dashboard.orders.order_sales_chart.heading');
    }

    protected function getOrderQuery(\DateTime $from = null, \DateTime $to = null)
    {
        return Order::whereNotNull('placed_at')
            ->whereBetween('placed_at', [
                $from,
                $to,
            ]);
    }

    protected function getOptions(): array
    {
        $currency = Currency::getDefault();
        $date = now()->settings([
            'monthOverflow' => false,
        ]);

        $from = $date->clone()->subYear();

        $orders = $this->getOrderQuery($from, $date)
            ->select(
                DB::RAW('SUM(total) as total'),
                DB::RAW('COUNT(*) as count'),
                DB::RAW('SUM(shipping_total) as shipping_total'),
                DB::RAW('SUM(discount_total) as discount_total'),
                DB::RAW('SUM(sub_total) as sub_total'),
                DB::RAW('SUM(tax_total) as tax_total'),
                DB::RAW(db_date('placed_at', '%M %Y', 'date')),
                DB::RAW(db_date('placed_at', '%Y-%m', 'sort_date')),
            )->groupBy(
                DB::RAW('date'),
                DB::RAW('sort_date'),
            )->orderBy(DB::RAW('sort_date'), 'asc')->get();

        $labels = [];
        $ordersData = [];
        $salesData = [];

        foreach ($orders as $order) {
            $labels[] = $order->date;
            $ordersData[] = $order->count;
            $salesData[] = $order->sub_total->decimal;
        }

        return [
            'chart' => [
                'type' => 'area',
                'stacked' => false,
                'toolbar' => [
                    'show' => false,
                ],
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'series' => [
                [
                    'name' => __('lunarpanel::widgets.dashboard.orders.order_sales_chart.series_one.label'),
                    'data' => $ordersData,
                ],
                [
                    'name' => __('lunarpanel::widgets.dashboard.orders.order_sales_chart.series_two.label'),
                    'data' => $salesData,
                ],
            ],
            'xaxis' => [
                'categories' => $labels,
            ],
            'yaxis' => [
                [
                    'seriesName' => 'OrderCount',
                    'min' => 0,
                    'decimalsInFloat' => 0,
                    'title' => [
                        'text' => __('lunarpanel::widgets.dashboard.orders.order_sales_chart.yaxis.series_one.label'),
                    ],
                ],
                [
                    'seriesName' => 'SalesRevenue',
                    'opposite' => true,
                    'title' => [
                        'text' => __('lunarpanel::widgets.dashboard.orders.order_sales_chart.yaxis.series_two.label', [
                            'currency' => $currency->code,
                        ]),
                    ],
                ],
            ],
        ];
    }
}
