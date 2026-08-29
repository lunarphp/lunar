<?php

namespace Lunar\Panel\Http\Controllers\Orders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Tag;
use Lunar\Panel\Http\Controllers\Concerns\ResolvesTableExtensions;

class OrderIndexController
{
    use ResolvesTableExtensions;

    /** Column keys the list may sort on; anything else falls back to the default. */
    protected array $sortable = ['created_at', 'placed_at', 'total'];

    /** Payment status keys, in ledger progression order (see core states.php). */
    protected array $paymentStatuses = ['pending', 'authorized', 'partially-paid', 'paid', 'partially-refunded', 'refunded', 'voided'];

    /** Fulfilment rollup status keys. */
    protected array $fulfilmentStatuses = ['unfulfilled', 'partially-fulfilled', 'fulfilled', 'partially-returned', 'returned'];

    /** @var array<int, array{key: string, label: string, width?: string, align?: string}> */
    protected array $columns = [];

    public function index(Request $request): Response
    {
        $this->columns = [
            ['key' => 'reference', 'label' => __('panel::orders.column_order'), 'width' => 'minmax(0,1fr)'],
            ['key' => 'placed_at', 'label' => __('panel::orders.column_date'), 'width' => '90px'],
            ['key' => 'customer', 'label' => __('panel::orders.column_customer'), 'width' => 'minmax(0,1.3fr)'],
            ['key' => 'items', 'label' => __('panel::orders.column_items'), 'width' => '70px', 'align' => 'right'],
            ['key' => 'payment_status', 'label' => __('panel::orders.column_payment'), 'width' => '130px'],
            ['key' => 'fulfilment_status', 'label' => __('panel::orders.column_fulfilment'), 'width' => '140px'],
            ['key' => 'tags', 'label' => __('panel::orders.column_tags'), 'width' => 'minmax(0,0.8fr)'],
            ['key' => 'total', 'label' => __('panel::orders.column_total'), 'width' => '110px', 'align' => 'right'],
        ];

        $sort = $request->string('sort')->value();
        $sort = in_array($sort, $this->sortable, true) ? $sort : 'created_at';
        $direction = $request->string('direction')->value() === 'asc' ? 'asc' : 'desc';

        $lifecycle = $request->filled('lifecycle') ? $request->string('lifecycle')->value() : 'open';

        $resolver = $this->resolveTable('orders.index');

        $defaultCurrency = Currency::getDefault();

        $orders = Order::query()
            ->with(['billingAddress', 'customer', 'tags', 'currency'])
            ->withSum('productLines as items_count', 'quantity')
            ->when($request->filled('q'), function ($query) use ($request, $resolver) {
                $term = $request->string('q')->value();
                $like = "%{$term}%";

                $query->where(function ($query) use ($like, $term, $resolver) {
                    $query->where('reference', 'like', $like)
                        ->orWhere('customer_reference', 'like', $like)
                        ->orWhereHas('billingAddress', fn ($query) => $query
                            ->where('first_name', 'like', $like)
                            ->orWhere('last_name', 'like', $like)
                            ->orWhere('company_name', 'like', $like)
                            ->orWhere('contact_email', 'like', $like)
                            ->orWhere('postcode', 'like', $like))
                        ->orWhereHas('customer', fn ($query) => $query
                            ->where('first_name', 'like', $like)
                            ->orWhere('last_name', 'like', $like));

                    $resolver->applySearchQueries($query, $term);
                });
            })
            ->when($request->filled('payment_status'), fn ($query) => $query->where('payment_status', $request->string('payment_status')))
            ->when($request->filled('fulfilment_status'), fn ($query) => $query->where('fulfilment_status', $request->string('fulfilment_status')))
            ->when($request->filled('channel_id'), fn ($query) => $query->where('channel_id', $request->integer('channel_id')))
            ->when($request->filled('tag'), fn ($query) => $query->whereHas('tags', fn ($query) => $query->where('value', strtoupper($request->string('tag')->value()))))
            // Orders are an inbox: default to the open work queue unless a
            // lifecycle is explicitly chosen ('all' lifts the constraint).
            ->when($lifecycle === 'open', fn ($query) => $query->whereNull('closed_at')->whereNull('cancelled_at'))
            ->when($lifecycle === 'closed', fn ($query) => $query->whereNotNull('closed_at')->whereNull('cancelled_at'))
            ->when($lifecycle === 'cancelled', fn ($query) => $query->whereNotNull('cancelled_at'))
            ->tap(fn ($query) => $this->applyDateRange($query, $request->string('date')->value()))
            ->tap(fn ($query) => $resolver->applyColumnQueries($query))
            ->tap(fn ($query) => $resolver->applyFilters($query, $request))
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString()
            ->through(function (Order $order) use ($resolver, $defaultCurrency) {
                $billing = $order->billingAddress;

                $customerName = $billing
                    ? (trim($billing->first_name.' '.$billing->last_name) ?: $billing->company_name)
                    : ($order->customer ? trim($order->customer->first_name.' '.$order->customer->last_name) : null);

                $currency = $order->currency ?? $defaultCurrency;

                $row = [
                    'id' => $order->id,
                    'reference' => $order->reference ?: '#'.$order->id,
                    'placed_at' => $order->placed_at,
                    'customer_name' => $customerName ?: null,
                    'customer_email' => $billing?->contact_email,
                    'items' => (int) $order->getAttribute('items_count'),
                    'payment_status' => $order->payment_status::$name,
                    'payment_status_label' => $order->payment_status->label(),
                    'fulfilment_status' => $order->fulfilment_status::$name,
                    'fulfilment_status_label' => $order->fulfilment_status->label(),
                    'cancelled' => $order->isCancelled(),
                    'tags' => $order->tags->pluck('value')->all(),
                    'total' => $currency ? (new PriceValue($order->total, $currency))->format() : null,
                    'show_url' => route('panel.orders.show', $order),
                    '_actions' => $resolver->resolveRowActionUrls($order),
                ];

                foreach ($resolver->getColumnKeys() as $key) {
                    $row[$key] = $order->getAttribute($key);
                }

                return $row;
            });

        return Inertia::render('orders/Index', [
            'orders' => $orders,
            ...$this->tableProps($resolver, $this->columns, $request),
            'channels' => Channel::orderBy('name')->get(['id', 'name']),
            'orderTags' => $this->orderTags(),
            'paymentOptions' => collect($this->paymentStatuses)->mapWithKeys(fn (string $name) => [$name => __("lunar::states.payment.$name")]),
            'fulfilmentOptions' => collect($this->fulfilmentStatuses)->mapWithKeys(fn (string $name) => [$name => __("lunar::states.fulfilment-status.$name")]),
            'totalCount' => Order::count(),
            'kpis' => $this->kpis(),
            'filters' => [
                ...$request->only(['q', 'payment_status', 'fulfilment_status', 'channel_id', 'tag', 'date', 'sort', 'direction']),
                'lifecycle' => $lifecycle,
            ],
            'urls' => [
                'index' => route('panel.orders.index'),
            ],
        ]);
    }

    /** Constrain the query to a preset placed-at window. */
    protected function applyDateRange(Builder $query, string $preset): void
    {
        match ($preset) {
            'today' => $query->whereDate('placed_at', now()->toDateString()),
            '7d' => $query->where('placed_at', '>=', now()->subDays(7)),
            '30d' => $query->where('placed_at', '>=', now()->subDays(30)),
            'this_month' => $query->where('placed_at', '>=', now()->startOfMonth()),
            'last_month' => $query->whereBetween('placed_at', [now()->subMonthNoOverflow()->startOfMonth(), now()->startOfMonth()]),
            'ytd' => $query->where('placed_at', '>=', now()->startOfYear()),
            default => null,
        };
    }

    /**
     * Distinct tag values in use on orders, for the tag filter dropdown.
     *
     * @return array<string, string>
     */
    protected function orderTags(): array
    {
        $orderType = (new Order)->getMorphClass();
        $prefix = config('lunar.database.table_prefix');

        return Tag::query()
            ->whereIn('id', fn ($query) => $query
                ->select('tag_id')
                ->from("{$prefix}taggables")
                ->where('taggable_type', $orderType))
            ->orderBy('value')
            ->pluck('value', 'value')
            ->all();
    }

    /**
     * KPI strip values, cached briefly since each is a whole-table aggregate.
     * Revenue and awaiting-fulfilment use the placed, default-currency basis
     * every order figure in the panel shares.
     *
     * @return array{orders30d: int, revenue30d: ?string, awaitingPayment: int, awaitingFulfilment: int}
     */
    protected function kpis(): array
    {
        return Cache::remember('lunar.panel.orders.kpis', now()->addMinutes(5), function (): array {
            $defaultCurrency = Currency::getDefault();

            $revenueMinor = (int) round((float) Order::query()
                ->whereNotNull('placed_at')
                ->where('placed_at', '>=', now()->subDays(30))
                ->selectRaw('COALESCE(SUM(total / NULLIF(exchange_rate, 0)), 0) AS revenue')
                ->value('revenue'));

            return [
                'orders30d' => Order::whereNotNull('placed_at')->where('placed_at', '>=', now()->subDays(30))->count(),
                'revenue30d' => $revenueMinor && $defaultCurrency
                    ? (new PriceValue($revenueMinor, $defaultCurrency))->format()
                    : null,
                'awaitingPayment' => Order::whereNotNull('placed_at')
                    ->whereIn('payment_status', ['pending', 'authorized'])
                    ->count(),
                'awaitingFulfilment' => Order::whereNotNull('placed_at')
                    ->where('payment_status', 'paid')
                    ->whereIn('fulfilment_status', ['unfulfilled', 'partially-fulfilled'])
                    ->count(),
            ];
        });
    }
}
