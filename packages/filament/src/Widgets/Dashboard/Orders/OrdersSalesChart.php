<?php

namespace Lunar\Filament\Widgets\Dashboard\Orders;

use Carbon\CarbonInterface;
use DateTime;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\LineChartWidget;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Order;
use Lunar\Filament\Widgets\Dashboard\Orders\Concerns\HasChartPalette;

class OrdersSalesChart extends LineChartWidget
{
    use HasChartPalette;
    use InteractsWithPageFilters;

    protected ?string $pollingInterval = '60s';

    protected ?string $heading = null;

    public function getHeading(): ?string
    {
        return __('lunar-filament::widgets.dashboard.orders.order_sales_chart.heading');
    }

    protected function getOrderQuery(DateTime|CarbonInterface|null $from = null, DateTime|CarbonInterface|null $to = null)
    {
        return Order::whereNotNull('placed_at')
            ->with(['currency'])
            ->whereBetween('placed_at', [
                $from,
                $to,
            ]);
    }

    protected function getData(): array
    {
        $date = now()->settings([
            'monthOverflow' => false,
        ]);

        $from = $date->clone()->subYear();

        $orders = $this->getOrderQuery($from, $date)
            ->select(
                DB::RAW('MAX(currency_code) as currency_code'),
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
            $salesData[] = $order->decimal('sub_total');
        }

        [$orders, $sales] = [$this->chartColor(0), $this->chartColor(1)];

        return [
            'datasets' => [
                [
                    'label' => __('lunar-filament::widgets.dashboard.orders.order_sales_chart.series_one.label'),
                    'data' => $ordersData,
                    'fill' => true,
                    'yAxisID' => 'y',
                    'borderColor' => $orders['border'],
                    'backgroundColor' => $orders['background'],
                ],
                [
                    'label' => __('lunar-filament::widgets.dashboard.orders.order_sales_chart.series_two.label'),
                    'data' => $salesData,
                    'fill' => true,
                    'yAxisID' => 'y1',
                    'borderColor' => $sales['border'],
                    'backgroundColor' => $sales['background'],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        $currency = Currency::getDefault();

        return [
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'position' => 'left',
                    'beginAtZero' => true,
                    'ticks' => ['precision' => 0],
                    'title' => [
                        'display' => true,
                        'text' => __('lunar-filament::widgets.dashboard.orders.order_sales_chart.yaxis.series_one.label'),
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'position' => 'right',
                    'beginAtZero' => true,
                    'grid' => ['drawOnChartArea' => false],
                    'title' => [
                        'display' => true,
                        'text' => __('lunar-filament::widgets.dashboard.orders.order_sales_chart.yaxis.series_two.label', [
                            'currency' => $currency->code,
                        ]),
                    ],
                ],
            ],
        ];
    }
}
