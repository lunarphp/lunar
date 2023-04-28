<?php

namespace Lunar\Hub\Http\Livewire\Components\Dashboard;

use Carbon\CarbonPeriod;
use DateTime;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Lunar\Models\Attribute;
use Lunar\Models\Currency;
use Lunar\Models\Customer;
use Lunar\Models\Order;

class SalesPerformance extends Component
{
    use WithPagination;

    /**
     * The date range.
     *
     * @var string
     */
    public ?string $range = null;

    public function rules()
    {
        return [
            'range' => 'string',
            'period' => 'string',
        ];
    }

    public function mount($to, $from)
    {
        $this->range = "{$from} to {$to}";
    }

    public function update()
    {
    }

    /**
     * Return the computed property for default currency.
     *
     * @return \Lunar\Models\Currency
     */
    public function getDefaultCurrencyProperty()
    {
        return Currency::getDefault();
    }

    public function getPeriodDateFormatProperty()
    {
        return $this->period == 'weekly' ? '%x-%v' : '%Y-%d';
    }

    /**
     * Return the computed sales performance property.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getGraphDataProperty()
    {
        [$from, $to] = explode(' to ', $this->range);

        $start = now()->parse($from);
        $end = now()->parse($to);

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


        return collect([
            'chart' => [
                'type' => 'area',
                'toolbar' => [
                    'show' => false,
                ],
                'height' => '100%',
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
                    'name' => 'This Period',
                    'data' => $thisPeriodDays->toArray(),
                ],
                [
                    'name' => 'Previous Period',
                    'data' => $previousPeriodDays->toArray(),
                ],
            ],
            'xaxis' => [
                'type' => 'datetime',
                'categories' => $days->toArray(),
            ],
            'yaxis' => [
                'title' => [
                    'text' => "Turnover {$this->defaultCurrency->code}",
                ],
            ],
            'tooltip' => [
                'x' => [
                    'format' => 'dd MMM yyyy',
                ],
            ],
        ]);
    }


    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.dashboard.sales-performance')
            ->layout('adminhub::layouts.base');
    }
}
