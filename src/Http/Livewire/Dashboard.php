<?php

namespace GetCandy\Hub\Http\Livewire;

use Asantibanez\LivewireCharts\Facades\LivewireCharts;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateTime;
use GetCandy\DataTypes\Price;
use GetCandy\Models\Currency;
use GetCandy\Models\Order;
use GetCandy\Models\OrderAddress;
use GetCandy\Models\Product;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    /**
     * The date range for the dashboard reports.
     *
     * @var array
     */
    public array $range = [
        'from' => null,
        'to' => null
    ];

    /**
     * {@inheritDoc}
     */
    protected $queryString = ['range'];

    public function mount()
    {
        $this->range['from'] = $this->range['from'] ?? now()->startOfWeek()->format('Y-m-d');
        $this->range['to'] = $this->range['too'] ?? now()->endOfWeek()->format('Y-m-d');
    }

    public function rules()
    {
        return [
            'range.from' => 'date',
            'range.to' => 'date,after:range.from',
        ];
    }
    /**
     * Get the computed property for new products count.
     *
     * @return int
     */
    public function getNewProductsCountProperty(): int
    {
        return Product::whereBetween('created_at', [
            now()->parse($this->range['from']),
            now()->parse($this->range['to']),
        ])->count();
    }

    /**
     * Return the computed property for customer percentage.
     *
     * @return int|float
     */
    public function getReturningCustomersPercentProperty()
    {
        $table = (new OrderAddress)->getTable();

        $query = DB::table($table)->where("{$table}.type", '=', 'billing')
            ->select(
                DB::RAW("COUNT(*) count"),
                "{$table}.contact_email"
            )
            ->whereBetween("{$table}.created_at", [
                now()->parse($this->range['from']),
                now()->parse($this->range['to']),
            ])->whereNotNull("{$table}.contact_email")
            ->leftJoin(
                DB::raw((new OrderAddress)->getTable().' address_join'),
                'address_join.contact_email',
                '=',
                "{$table}.contact_email"
            )->groupBy("{$table}.id");

        $total = $query->clone()->get()->count();

        $returning = $query->clone()->having('count', '<=', 1)->count();

        if (!$returning) {
            return 0;
        }

        return round(($returning / $total) * 100, 2);
    }

    /**
     * Return computed property for order count.
     *
     * @return string
     */
    public function getOrderCountProperty()
    {
        return number_format(Order::whereBetween('placed_at', [
            now()->parse($this->range['from']),
            now()->parse($this->range['to']),
        ])->count(), 0);
    }

    /**
     * Return computed property for order totals.
     *
     * @return \GetCandy\DataTypes\Price
     */
    public function getOrderTotalProperty()
    {
        $query = Order::whereBetween('placed_at', [
            now()->parse($this->range['from']),
            now()->parse($this->range['to']),
        ])->select(
            DB::RAW('SUM(sub_total) as total')
        )->first();

        return new Price($query->total->value, Currency::getDefault(), 1);
    }

    public function getSalesPerformanceProperty()
    {
        $start = now()->parse($this->range['from']);
        $end = now()->parse($this->range['to']);

        $thisPeriod = Order::select(
            DB::RAW('SUM(sub_total) as sub_total'),
            DB::RAW("DATE_FORMAT(placed_at, '%Y-%m') as format_date")
        )->whereNotNull('placed_at')
        ->whereBetween('placed_at', [
            $start,
            $end,
        ])->groupBy('format_date')->get();

        $previousPeriod = Order::select(
            DB::RAW('SUM(sub_total) as sub_total'),
            DB::RAW("DATE_FORMAT(placed_at, '%Y-%m') as format_date")
        )->whereNotNull('placed_at')
        ->whereBetween('placed_at', [
            $start->clone()->subYear(),
            $end->clone()->subYear(),
        ])->groupBy('format_date')->get();

        $period = CarbonPeriod::create($start, '1 month', $end);

        $thisPeriodMonths = collect();
        $previousPeriodMonths = collect();
        $months = collect();

        foreach ($period as $datetime) {
            $months->push($datetime->toDateTimeString());
            // Do we have some totals for this month?
            if ($totals = $thisPeriod->first(fn($p) => $p->format_date == $datetime->format('Y-m'))) {
                $thisPeriodMonths->push($totals->sub_total->decimal);
            } else {
                $thisPeriodMonths->push(0);
            }
            if ($prevTotals = $previousPeriod->first(fn($p) => $p->format_date == $datetime->format('Y-m'))) {
                $previousPeriodMonths->push($prevTotals->sub_total->decimal);
            } else {
                $previousPeriodMonths->push(0);
            }
        }

        $currency = Currency::getDefault();

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
                    'data' => $thisPeriodMonths->toArray(),
                ],
                [
                    'name' => 'Previous Period',
                    'data' => $previousPeriodMonths->toArray(),
                ],
            ],
            'xaxis' => [
                'type' => 'datetime',
                'categories' => $months->toArray(),
            ],
            'yaxis' => [
                'title' => [
                    'text' => "Turnover {$currency->code}",
                ],
            ],
            'tooltip' => [
                'x' => [
                    'format' => 'dd MMM yyyy',
                ],
            ],
        ]);


    }



    public function render()
    {
        $categories = [];

        for ($i = 0; $i < 10; $i++) {
            $categories[] = Carbon::now()->addDays($i)->toDateTimeString();
        }

        // Sales Performance
        $options1 = collect([
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
                    'data' => [3000, 4000, 3500, 5000, 4900, 6000, 7000, 9100, 12500, 6300],
                ],
                [
                    'name' => 'Previous Period',
                    'data' => [2000, 3000, 4500, 4000, 2900, 7000, 8000, 4100, 9500, 9400],
                ],
            ],
            'xaxis' => [
                'type' => 'datetime',
                'categories' => $categories,
            ],
            'yaxis' => [
                'title' => [
                    'text' => 'Turnover $ USD',
                ],
            ],
            'tooltip' => [
                'x' => [
                    'format' => 'dd MMM yyyy',
                ],
            ],
        ]);

        // Customer Group Orders
        $options2 = collect([
            'chart' => [
                'type' => 'donut',
                'toolbar' => [
                    'show' => false,
                ],
                'height' => '100%',
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'series' => [104, 55, 13, 33],
            'labels' => ['Guest', 'Retail', 'Trade', 'Distributor'],
            'legend' => [
                'position' => 'bottom',
            ],
        ]);

        return view('adminhub::livewire.dashboard')
            ->with('options1', $options1)
            ->with('options2', $options2);
    }
}
