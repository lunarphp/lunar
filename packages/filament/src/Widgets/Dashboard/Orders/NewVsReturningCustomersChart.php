<?php

namespace Lunar\Filament\Widgets\Dashboard\Orders;

use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use DateTime;
use Filament\Widgets\BarChartWidget;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Order;
use Lunar\Filament\Widgets\Dashboard\Orders\Concerns\HasChartPalette;

class NewVsReturningCustomersChart extends BarChartWidget
{
    use HasChartPalette;

    protected ?string $pollingInterval = '60s';

    protected ?string $heading = null;

    public function getHeading(): ?string
    {
        return __('lunar-filament::widgets.dashboard.orders.new_returning_customers.heading');
    }

    protected function getOrderQuery(DateTime|CarbonInterface|null $from = null, DateTime|CarbonInterface|null $to = null)
    {
        return Order::whereNotNull('placed_at')
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
                        WHEN NOT new_customer THEN 1
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
            )->orderBy(DB::RAW(db_date('placed_at', '%Y-%m')), 'desc')->get();

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

        [$new, $returning] = [$this->chartColor(0), $this->chartColor(1)];

        return [
            'datasets' => [
                [
                    'label' => __('lunar-filament::widgets.dashboard.orders.new_returning_customers.series_one.label'),
                    'data' => $newCustomers,
                    'backgroundColor' => $new['border'],
                ],
                [
                    'label' => __('lunar-filament::widgets.dashboard.orders.new_returning_customers.series_two.label'),
                    'data' => $returningCustomers,
                    'backgroundColor' => $returning['border'],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => ['stacked' => true],
                'y' => [
                    'stacked' => true,
                    'title' => [
                        'display' => true,
                        'text' => '# Customers',
                    ],
                ],
            ],
        ];
    }
}
