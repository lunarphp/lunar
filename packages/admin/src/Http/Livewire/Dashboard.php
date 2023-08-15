<?php

namespace Lunar\Hub\Http\Livewire;

use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Lunar\DataTypes\Price;
use Lunar\Facades\DB;
use Lunar\Models\Currency;
use Lunar\Models\Customer;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Order;
use Lunar\Models\OrderLine;
use Lunar\Models\Product;

class Dashboard extends Component
{
    /**
     * The date to query from.
     */
    protected string $from;

    /**
     * The date to query too.
     */
    protected string $to;

    public function mount()
    {
        $this->from = now()->subDays(14)->format('Y-m-d');
        $this->to = now()->format('Y-m-d');
    }

    /**
     * Get the computed property for new products count.
     */
    public function getNewProductsCountProperty(): int
    {
        return Product::whereBetween('created_at', [
            now()->parse($this->from),
            now()->parse($this->to),
        ])->count();
    }

    /**
     * Return the computed property for customer percentage.
     *
     * @return int|float
     */
    public function getReturningCustomersPercentProperty()
    {
        $orders = Cache::remember('dashboard:returning_customers', now()->addDay(), function () {
            return Order::select(
                DB::RAW('COUNT(*) as count'),
                'new_customer'
            )->whereBetween('created_at', [
                now()->parse($this->from),
                now()->parse($this->to),
            ])->groupBy('new_customer')->get();
        });

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
            now()->parse($this->from),
            now()->parse($this->to),
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
            now()->parse($this->from),
            now()->parse($this->to),
        ])->select(
            DB::RAW('SUM(sub_total) as total')
        )->first();

        return new Price($query->total->value, $this->defaultCurrency, 1);
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
            'description',
            'identifier',
            'purchasable_type',
            'purchasable_id',
            DB::RAW('COUNT(*) as count'),
        ])->join(
            $orderTable,
            'order_id',
            '=',
            "{$orderTable}.id"
        )->whereBetween("{$orderTable}.placed_at", [
            now()->parse($this->from),
            now()->parse($this->to),
        ])->where('type', '!=', 'shipping')
            ->groupBy('purchasable_type', 'purchasable_id', 'description', 'identifier')
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
                now()->parse($this->from),
                now()->parse($this->to),
            ])->groupBy('ccg.customer_group_id')
            ->get();

        $customerGroups = CustomerGroup::get();

        $labels = $customerGroups->map(
            fn (CustomerGroup $group) => $group->name
        )->toArray();

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
