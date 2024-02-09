<?php

namespace Lunar\Admin\Filament\Widgets\Dashboard\Orders;

use Carbon\CarbonPeriod;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Lunar\Facades\DB;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Order;

class AverageOrderValueChart extends ApexChartWidget
{
    /**
     * Chart Id
     */
    protected static ?string $chartId = 'averageOrderValue';

    protected function getHeading(): ?string
    {
        return __('lunarpanel::widgets.dashboard.orders.average_order_value.heading');
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

        $customerGroups = CustomerGroup::get();

        $date = now()->settings([
            'monthOverflow' => false,
        ]);

        $from = $date->clone()->subYear();

        $period = CarbonPeriod::create($from, '1 month', $date);

        $series = $customerGroups->mapWithKeys(function ($group) use ($date, $from, $period) {
            $query = $this->getOrderQuery($from, $date);

            /**
             * $format = '%Y-%m';
             * $displayFormat = '%M %Y';
             */
            $guestOrders = collect();

            if ($group->default) {
                $guestOrders = $query->clone()->whereNull('user_id')->whereNull('customer_id')
                    ->select(
                        DB::RAW('ROUND(AVG(total), 0) as total'),
                        DB::RAW('ROUND(AVG(shipping_total), 0) as shipping_total'),
                        DB::RAW('ROUND(AVG(discount_total), 0) as discount_total'),
                        DB::RAW('ROUND(AVG(sub_total), 0) as sub_total'),
                        DB::RAW('ROUND(AVG(tax_total), 0) as tax_total'),
                        DB::RAW(db_date('placed_at', '%Y-%m', 'date'))
                    )->groupBy(
                        DB::RAW('date')
                    )->orderBy(DB::RAW('date'), 'desc')->get();
            }

            $result = $query->whereHas(
                'customer',
                fn ($relation) => $relation->whereHas(
                    'customerGroups',
                    fn ($subRelation) => $subRelation->where("{$group->getTable()}.id", '=', $group->id)
                )
            )->select(
                DB::RAW('ROUND(AVG(total), 0) as total'),
                DB::RAW('ROUND(AVG(shipping_total), 0) as shipping_total'),
                DB::RAW('ROUND(AVG(discount_total), 0) as discount_total'),
                DB::RAW('ROUND(AVG(sub_total), 0) as sub_total'),
                DB::RAW('ROUND(AVG(tax_total), 0) as tax_total'),
                DB::RAW(db_date('placed_at', '%Y-%m', 'date'))
            )->groupBy(
                DB::RAW('date')
            )->orderBy(DB::RAW('date'), 'desc')->get();

            $merged = collect([
                ...$result,
                ...$guestOrders,
            ]);

            $data = collect();

            foreach ($period as $date) {
                $result = $merged->first(function ($month) use ($date) {
                    return $month->date == $date->format('Y-m');
                });
                $data->push($result?->sub_total->decimal ?: 0);
            }

            return [
                $group->handle => [
                    'name' => $group->name,
                    'data' => $data,
                ],
            ];
        });

        $labels = [];

        foreach ($period as $date) {
            $labels[] = $date->format('F Y');
        }

        $currency = Currency::getDefault();

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
            'series' => $series->values()->toArray(),
            'xaxis' => [
                'categories' => $labels,
            ],
            'yaxis' => [
                'title' => [
                    'text' => __('lunarpanel::widgets.dashboard.orders.order_totals_chart.yaxis.label', [
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
            )->orderBy(DB::RAW(db_date('placed_at', '%Y-%m')), 'desc')->get();

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
