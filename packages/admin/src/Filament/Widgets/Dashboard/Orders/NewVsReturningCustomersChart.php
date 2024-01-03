<?php

namespace Lunar\Admin\Filament\Widgets\Dashboard\Orders;

use Carbon\CarbonPeriod;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Lunar\Facades\DB;
use Lunar\Models\Currency;
use Lunar\Models\Order;

class NewVsReturningCustomersChart extends ApexChartWidget
{
    /**
     * Chart Id
     */
    protected static string $chartId = 'newVsReturningCustomers';

    protected function getHeading(): ?string
    {
        return __('lunarpanel::widgets.dashboard.orders.new_returning_customers.heading');
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

        $period = CarbonPeriod::create($from, '1 month', $date);

        $results = $this->getOrderQuery($from, $date)
            ->select(
                DB::RAW('SUM(
                    CASE
                        WHEN new_customer THEN 1 
                        ELSE 0
                    END
                ) as new_customer_count'),
                DB::RAW('SUM(
                    CASE
                        WHEN !new_customer THEN 1 
                        ELSE 0
                    END
                ) as returning_customer_count'),
                DB::RAW('COUNT(*) as total'),
                DB::RAW(db_date('placed_at', '%M', 'month')),
                DB::RAW(db_date('placed_at', '%Y', 'year')),
                DB::RAW(db_date('placed_at', '%Y%m', 'monthstamp'))
            )->groupBy(
                DB::RAW('month'),
                DB::RAW('year'),
                DB::RAW('monthstamp'),
                DB::RAW(db_date('placed_at', '%Y-%m')),
            )->orderBy(DB::RAW("DATE_FORMAT(placed_at, '%Y-%m')"), 'desc')->get();

        $labels = [];
        $newCustomers = [];
        $returningCustomers = [];

        foreach ($period as $date) {
            $labels[] = $date->format('F Y');
            $report = $results->first(function ($month) use ($date) {
                return $month->monthstamp == $date->format('Ym');
            });

            $returningCustomers[] = (int) $report?->returning_customer_count ?: 0;
            $newCustomers[] = (int) $report?->new_customer_count ?: 0;
        }

        return [
            'chart' => [
                'type' => 'bar',
                'stacked' => true,
                'toolbar' => [
                    'show' => false,
                ],
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'series' => [
                [
                    'name' => __('lunarpanel::widgets.dashboard.orders.new_returning_customers.series_one.label'),
                    'data' => $newCustomers,
                ],
                [
                    'name' => __('lunarpanel::widgets.dashboard.orders.new_returning_customers.series_two.label'),
                    'data' => $returningCustomers,
                ],
            ],
            'xaxis' => [
                'categories' => $labels,
            ],
            'yaxis' => [
                'title' => [
                    'text' => '# Customers',
                ],
            ],
            'tooltip' => [
                'x' => [
                    'format' => 'dd MMM yyyy',
                ],
            ],
        ];
    }

    protected function getTotalsForPeriod($from, $to)
    {
        $currentPeriod = collect();
        $period = CarbonPeriod::create($from, '1 month', $to);

        $results = $this->getOrderQuery($from, $to)
            ->select(
                DB::RAW('SUM(total) as total'),
                DB::RAW('SUM(shipping_total) as shipping_total'),
                DB::RAW('SUM(discount_total) as discount_total'),
                DB::RAW('SUM(sub_total) as sub_total'),
                DB::RAW('SUM(tax_total) as tax_total'),
                DB::RAW(db_date('placed_at', '%M', 'month')),
                DB::RAW(db_date('placed_at', '%Y', 'year')),
                DB::RAW(db_date('placed_at', '%Y%m', 'monthstamp'))
            )->groupBy(
                DB::RAW('month'),
                DB::RAW('year'),
                DB::RAW('monthstamp'),
                DB::RAW(db_date('placed_at', '%Y-%m')),
            )->orderBy(DB::RAW("DATE_FORMAT(placed_at, '%Y-%m')"), 'desc')->get();

        foreach ($period as $date) {
            // Find our records for this period.
            $report = $results->first(function ($month) use ($date) {
                return $month->monthstamp == $date->format('Ym');
            });
            $currentPeriod->push((object) [
                'order_total' => $report?->total->decimal ?: 0,
                'shipping_total' => $report?->shipping_total->decimal ?: 0,
                'discount_total' => $report?->discount_total->decimal ?: 0,
                'sub_total' => $report?->sub_total->decimal ?: 0,
                'month' => $date->format('F'),
                'year' => $date->format('Y'),
                'tax_total' => $report?->tax_total->decimal ?: 0,
            ]);
        }

        return $currentPeriod;
    }
}
