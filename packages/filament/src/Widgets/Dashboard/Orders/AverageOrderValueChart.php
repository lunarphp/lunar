<?php

namespace Lunar\Filament\Widgets\Dashboard\Orders;

use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use DateTime;
use Filament\Widgets\LineChartWidget;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Order;
use Lunar\Filament\Widgets\Dashboard\Orders\Concerns\HasChartPalette;

class AverageOrderValueChart extends LineChartWidget
{
    use HasChartPalette;

    protected ?string $pollingInterval = '60s';

    protected ?string $heading = null;

    public function getHeading(): ?string
    {
        return __('lunar-filament::widgets.dashboard.orders.average_order_value.heading');
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
        $customerGroups = CustomerGroup::get();

        $date = now()->settings([
            'monthOverflow' => false,
        ]);

        $from = $date->clone()->subYear();

        $period = CarbonPeriod::create($from, '1 month', $date);

        $datasets = $customerGroups->values()->map(function ($group, $index) use ($date, $from, $period) {
            $query = $this->getOrderQuery($from, $date);

            $guestOrders = collect();

            if ($group->default) {
                $guestOrders = $query->clone()->with(['currency'])->whereNull('user_id')->whereNull('customer_id')
                    ->select(
                        DB::RAW('MAX(currency_code) as currency_code'),
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
                DB::RAW('MAX(currency_code) as currency_code'),
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

            $color = $this->chartColor($index);

            return [
                'label' => $group->name,
                'data' => $data->all(),
                'fill' => true,
                'borderColor' => $color['border'],
                'backgroundColor' => $color['background'],
            ];
        });

        $labels = [];

        foreach ($period as $date) {
            $labels[] = $date->format('F Y');
        }

        return [
            'datasets' => $datasets->values()->all(),
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        $currency = Currency::getDefault();

        return [
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
}
