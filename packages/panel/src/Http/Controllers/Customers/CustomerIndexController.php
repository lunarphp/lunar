<?php

namespace Lunar\Panel\Http\Controllers\Customers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Order;
use Lunar\Panel\Http\Controllers\Concerns\ResolvesTableExtensions;

class CustomerIndexController
{
    use ResolvesTableExtensions;

    /** @var string[] */
    protected array $sortable = ['first_name', 'last_name', 'company_name', 'created_at'];

    /** @var array<int, array{key: string, label: string, width?: string, align?: string}> */
    protected array $columns = [];

    public function index(Request $request): Response
    {
        $this->columns = [
            ['key' => 'full_name', 'label' => __('panel::customers.column_customer'), 'width' => 'minmax(0,1.6fr)'],
            ['key' => 'company_name', 'label' => __('panel::customers.column_company'), 'width' => 'minmax(0,1fr)'],
            ['key' => 'customer_groups', 'label' => __('panel::customers.column_groups'), 'width' => 'minmax(0,1fr)'],
            ['key' => 'orders_count', 'label' => __('panel::customers.stat_orders'), 'width' => '80px', 'align' => 'right'],
            ['key' => 'total_spend', 'label' => __('panel::customers.stat_total_spend'), 'width' => '120px', 'align' => 'right'],
            ['key' => 'last_order_at', 'label' => __('panel::customers.stat_latest_order'), 'width' => '110px'],
        ];

        $sort = $request->string('sort')->value();
        $sort = in_array($sort, $this->sortable, true) ? $sort : 'created_at';

        $direction = $request->string('direction')->value() === 'asc' ? 'asc' : 'desc';

        $resolver = $this->resolveTable('customers.index');

        $defaultCurrency = Currency::getDefault();

        $customerKey = (new Customer)->qualifyColumn('id');

        // Per-row order stats as correlated subqueries; the same placed-order
        // and default-currency basis as the edit page's lifetime stats.
        $placedOrders = fn () => Order::query()
            ->whereColumn('customer_id', $customerKey)
            ->whereNotNull('placed_at');

        $customers = Customer::query()
            ->select((new Customer)->qualifyColumn('*'))
            ->addSelect([
                'placed_orders_count' => $placedOrders()->selectRaw('COUNT(*)'),
                'total_spend_minor' => $placedOrders()->selectRaw('COALESCE(SUM(total / NULLIF(exchange_rate, 0)), 0)'),
                'last_order_at' => $placedOrders()->select('placed_at')->latest('placed_at')->limit(1),
            ])
            ->with('customerGroups:id,name')
            ->with(['users' => fn ($query) => $query->select(
                $query->getModel()->qualifyColumn('id'),
                $query->getModel()->qualifyColumn('email'),
            )])
            ->when($request->filled('q'), function ($query) use ($request, $resolver) {
                $term = $request->string('q')->value();
                $like = "%{$term}%";

                $query->where(function ($query) use ($like, $term, $resolver) {
                    $query->where('first_name', 'like', $like)
                        ->orWhere('last_name', 'like', $like)
                        ->orWhere('company_name', 'like', $like)
                        ->orWhere('tax_identifier', 'like', $like)
                        ->orWhere('account_ref', 'like', $like)
                        ->orWhereHas('users', fn ($query) => $query->where('email', 'like', $like));

                    $resolver->applySearchQueries($query, $term);
                });
            })
            ->when($request->filled('customer_group_id'), fn ($query) => $query->whereHas(
                'customerGroups',
                fn ($query) => $query->where($query->getModel()->qualifyColumn('id'), $request->integer('customer_group_id')),
            ))
            ->when($request->string('type')->value() === 'business', fn ($query) => $query->whereNotNull('company_name')->where('company_name', '!=', ''))
            ->when($request->string('type')->value() === 'individual', fn ($query) => $query->where(fn ($query) => $query->whereNull('company_name')->orWhere('company_name', '')))
            ->tap(fn ($query) => $resolver->applyColumnQueries($query))
            ->tap(fn ($query) => $resolver->applyFilters($query, $request))
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString()
            ->through(function (Customer $customer) use ($resolver, $defaultCurrency) {
                $ordersCount = (int) $customer->getAttribute('placed_orders_count');
                $spendMinor = (int) round((float) $customer->getAttribute('total_spend_minor'));

                $row = [
                    'id' => $customer->id,
                    'full_name' => $customer->full_name,
                    'first_name' => $customer->first_name,
                    'last_name' => $customer->last_name,
                    'company_name' => $customer->company_name,
                    'account_ref' => $customer->account_ref,
                    'email' => $customer->users->first()?->email,
                    'created_at' => $customer->created_at,
                    'orders_count' => $ordersCount,
                    'total_spend' => $ordersCount && $defaultCurrency
                        ? (new PriceValue($spendMinor, $defaultCurrency))->format()
                        : null,
                    'last_order_at' => $customer->getAttribute('last_order_at'),
                    'customer_groups' => $customer->customerGroups->map(fn (CustomerGroup $group) => [
                        'id' => $group->id,
                        'name' => $group->name,
                    ]),
                    'edit_url' => route('panel.customers.edit', $customer),
                    '_actions' => $resolver->resolveRowActionUrls($customer),
                ];

                foreach ($resolver->getColumnKeys() as $key) {
                    $row[$key] = $customer->getAttribute($key);
                }

                return $row;
            });

        return Inertia::render('customers/Index', [
            'customers' => $customers,
            ...$this->tableProps($resolver, $this->columns, $request),
            'customerGroups' => CustomerGroup::all(['id', 'name']),
            'totalCount' => Customer::count(),
            'kpis' => $this->kpis(),
            'filters' => $request->only(['q', 'customer_group_id', 'type', 'sort', 'direction']),
            'urls' => [
                'index' => route('panel.customers.index'),
                'create' => route('panel.customers.create'),
            ],
        ]);
    }

    /**
     * KPI strip values. Everything here is an aggregate over whole tables, so
     * the block is cached briefly: on stores with very large customer or order
     * tables these queries are the expensive part of the page, and the strip
     * does not need to be second-accurate. Average lifetime value is a single
     * aggregate over placed orders (default-currency basis, like everywhere
     * else); its delta compares against the average as it stood 30 days ago.
     *
     * @return array{total: int, newLast30Days: int, business: int, avgLifetimeValue: ?string, avgLifetimeValueDelta: ?int}
     */
    protected function kpis(): array
    {
        return Cache::remember('lunar.panel.customers.kpis', now()->addMinutes(5), function (): array {
            $lifetime = fn () => Order::query()
                ->whereNotNull('placed_at')
                ->whereNotNull('customer_id')
                ->selectRaw('COALESCE(SUM(total / NULLIF(exchange_rate, 0)), 0) AS spend, COUNT(DISTINCT customer_id) AS customers');

            $current = $lifetime()->first();
            $prior = $lifetime()->where('placed_at', '<', now()->subDays(30))->first();

            $average = fn ($row): int => $row->customers ? (int) round($row->spend / $row->customers) : 0;

            $currentAverage = $average($current);
            $priorAverage = $average($prior);

            $defaultCurrency = Currency::getDefault();

            return [
                'total' => Customer::count(),
                'newLast30Days' => Customer::where('created_at', '>=', now()->subDays(30))->count(),
                'business' => Customer::whereNotNull('company_name')->where('company_name', '!=', '')->count(),
                'avgLifetimeValue' => $currentAverage && $defaultCurrency
                    ? (new PriceValue($currentAverage, $defaultCurrency))->format()
                    : null,
                'avgLifetimeValueDelta' => $priorAverage > 0
                    ? (int) round(($currentAverage - $priorAverage) / $priorAverage * 100)
                    : null,
            ];
        });
    }
}
