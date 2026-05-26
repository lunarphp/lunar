<?php

namespace Lunar\Filament\Widgets\Dashboard\Orders;

use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use DateTime;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\LineChartWidget;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Order;
use Lunar\Filament\Widgets\Dashboard\Orders\Concerns\HasChartPalette;

class OrderTotalsChart extends LineChartWidget
{
    use HasChartPalette;
    use InteractsWithPageFilters;

    protected ?string $pollingInterval = '60s';

    protected ?string $heading = null;

    public function getHeading(): ?string
    {
        return __('lunar-filament::widgets.dashboard.orders.order_totals_chart.heading');
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

        $currentPeriod = $this->getTotalsForPeriod($from, $date);
        $previousPeriod = $this->getTotalsForPeriod($from->clone()->subYear(), $date->clone()->subYear());

        [$current, $previous] = [$this->chartColor(0), $this->chartColor(1)];

        return [
            'datasets' => [
                [
                    'label' => __('lunar-filament::widgets.dashboard.orders.order_totals_chart.series_one.label'),
                    'data' => $currentPeriod->pluck('sub_total')->all(),
                    'fill' => true,
                    'borderColor' => $current['border'],
                    'backgroundColor' => $current['background'],
                ],
                [
                    'label' => __('lunar-filament::widgets.dashboard.orders.order_totals_chart.series_two.label'),
                    'data' => $previousPeriod->pluck('sub_total')->all(),
                    'fill' => true,
                    'borderColor' => $previous['border'],
                    'backgroundColor' => $previous['background'],
                ],
            ],
            'labels' => $previousPeriod->map(fn ($record) => $record->month)->all(),
        ];
    }

    protected function getOptions(): array
    {
        $currency = Currency::getDefault();

        return [
            'plugins' => [
                'legend' => ['display' => true],
            ],
            'scales' => [
                'y' => [
                    'title' => [
                        'display' => true,
                        'text' => __('lunar-filament::widgets.dashboard.orders.order_totals_chart.yaxis.label', [
                            'currency' => $currency->code,
                        ]),
                    ],
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
                DB::RAW('MAX(currency_code) as currency_code'),
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
            $report = $results->first(function ($month) use ($date) {
                return $month->monthstamp == $date->format('Ym');
            });
            $currentPeriod->push((object) [
                'order_total' => $report?->decimal('total') ?: 0,
                'shipping_total' => $report?->decimal('shipping_total') ?: 0,
                'discount_total' => $report?->decimal('discount_total') ?: 0,
                'sub_total' => $report?->decimal('sub_total') ?: 0,
                'month' => $date->format('F'),
                'year' => $date->format('Y'),
                'tax_total' => $report?->decimal('tax_total') ?: 0,
            ]);
        }

        return $currentPeriod;
    }
}
