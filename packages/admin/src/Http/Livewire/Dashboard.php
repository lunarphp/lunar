<?php

namespace Lunar\Hub\Http\Livewire;

use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Lunar\DataTypes\Price;
use Lunar\Models\Currency;
use Lunar\Models\Customer;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Order;
use Lunar\Models\OrderLine;
use Lunar\Models\Product;

class Dashboard extends Component
{
    /**
     * The date range for the dashboard reports.
     *
     * @var array
     */
    public array $range = [
        'from' => null,
        'to' => null,
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
        $orders = Order::select(
            DB::RAW('COUNT(*) as count'),
            'new_customer'
        )->whereBetween('created_at', [
            now()->parse($this->range['from']),
            now()->parse($this->range['to']),
        ])->groupBy('new_customer')->get();

        if ($orders->isEmpty()) {
            return 0;
        }

        $returning = $orders->first(fn ($order) => ! $order->new_customer);
        $new = $orders->first(fn ($order) => $order->new_customer);

        if (! $returning || ! $returning->count) {
            return 0;
        }

        if (! $new || ! $new->count) {
            return 100;
        }

        return round(($returning->count / ($new->count + $returning->count)) * 100, 2);
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
     * Return the computed property for default currency.
     *
     * @return \Lunar\Models\Currency
     */
    public function getDefaultCurrencyProperty()
    {
        return Currency::getDefault();
    }

    /**
     * Return computed property for order totals.
     *
     * @return \Lunar\DataTypes\Price
     */
    public function getOrderTotalProperty()
    {
        $query = Order::whereBetween('placed_at', [
            now()->parse($this->range['from']),
            now()->parse($this->range['to']),
        ])->select(
            DB::RAW('SUM(sub_total) as total')
        )->first();

        return new Price($query->total->value, $this->defaultCurrency, 1);
    }

    /**
     * Return the computed sales performance property.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getSalesPerformanceProperty()
    {
        $start = now()->parse($this->range['from']);
        $end = now()->parse($this->range['to']);

        $thisPeriod = Order::select(
            DB::RAW('SUM(sub_total) as sub_total'),
            db_date('placed_at', '%Y-%m', 'format_date')
        )->whereNotNull('placed_at')
        ->whereBetween('placed_at', [
            $start,
            $end,
        ])->groupBy('format_date')->get();

        $previousPeriod = Order::select(
            DB::RAW('SUM(sub_total) as sub_total'),
            db_date('placed_at', '%Y-%m', 'format_date')
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
            if ($totals = $thisPeriod->first(fn ($p) => $p->format_date == $datetime->format('Y-m'))) {
                $thisPeriodMonths->push($totals->sub_total->decimal);
            } else {
                $thisPeriodMonths->push(0);
            }
            if ($prevTotals = $previousPeriod->first(fn ($p) => $p->format_date == $datetime->format('Y-m'))) {
                $previousPeriodMonths->push($prevTotals->sub_total->decimal);
            } else {
                $previousPeriodMonths->push(0);
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
     * Return the computed property for recent orders.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRecentOrdersProperty()
    {
        return Order::withCount(['lines'])
            ->orderBy('placed_at', 'desc')
            ->take(6)
            ->get();
    }

    /**
     * Return the computed property for top selling products.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getTopSellingProductsProperty()
    {
        $orderTable = (new Order())->getTable();

        return OrderLine::with(['purchasable'])->select([
            'purchasable_type',
            'purchasable_id',
            DB::RAW('COUNT(*) as count'),
        ])->join(
            $orderTable,
            'order_id',
            '=',
            "{$orderTable}.id"
        )->whereBetween("{$orderTable}.placed_at", [
            now()->parse($this->range['from']),
            now()->parse($this->range['to']),
        ])->where('type', '!=', 'shipping')
        ->groupBy('purchasable_type', 'purchasable_id')
        ->orderBy('count', 'desc')
        ->take(2)->get();
    }

    /**
     * Return computed property for customer group orders.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCustomerGroupOrdersProperty()
    {
        $userModel = config('auth.providers.users.model');

        $ordersTable = (new Order())->getTable();
        $usersTable = (new $userModel())->getTable();
        $customer = (new Customer());
        $customersTable = $customer->getTable();
        $customerUserTable = $customer->users()->getTable();
        $customerCustomerGroupTable = $customer->customerGroups()->getTable();

        $orders = DB::connection((new Order())->getConnectionName())
            ->table($ordersTable, 'o')
            ->selectRaw('
                ccg.customer_group_id,
                count(o.id) as order_count
            ')->leftJoin(
                DB::raw("{$usersTable} u"),
                'o.user_id',
                '=',
                'u.id'
            )->leftJoin(
                DB::RAW("{$customerUserTable} cu"),
                'cu.user_id',
                '=',
                'u.id'
            )->leftJoin(
                DB::RAW("{$customersTable} c"),
                'cu.customer_id',
                '=',
                'c.id'
            )->leftJoin(
                DB::RAW("{$customerCustomerGroupTable} ccg"),
                'c.id',
                '=',
                'ccg.customer_id'
            )->whereBetween('placed_at', [
                now()->parse($this->range['from']),
                now()->parse($this->range['to']),
            ])->groupBy('ccg.customer_group_id')
            ->get();

        $customerGroups = CustomerGroup::get();

        $labels = $customerGroups->pluck('name')->toArray();

        $series = collect();

        foreach ($customerGroups as $group) {
            // Find our counts...
            $data = $orders->filter(function ($row) use ($group) {
                if ($group->default && ! $row->customer_group_id) {
                    return true;
                }

                return $group->id == $row->customer_group_id;
            });

            $series->push($data->sum('order_count'));
        }

        return collect([
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
            'series' => $series->toArray(),
            'labels' => $labels,
            'legend' => [
                'position' => 'bottom',
            ],
        ]);
    }

    public function render()
    {
        return view('adminhub::livewire.dashboard');
    }
}
