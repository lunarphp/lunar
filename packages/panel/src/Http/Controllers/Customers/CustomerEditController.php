<?php

namespace Lunar\Panel\Http\Controllers\Customers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Contracts\Actions\Customers\DeletesCustomer;
use Lunar\Core\Contracts\Actions\Customers\UpdatesCustomer;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\Address;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Order;
use Lunar\Panel\Contracts\DraftManager;
use Lunar\Panel\Http\Requests\Customers\CustomerRequest;
use Lunar\Panel\PanelManager;
use Lunar\Panel\Support\TimelineActivity;
use Spatie\Activitylog\Models\Activity;

class CustomerEditController
{
    /**
     * Order-value chart ranges: window length and bucket granularity.
     *
     * @var array<string, array{months: int, bucket: 'month'|'quarter'|'year'}>
     */
    protected const CHART_RANGES = [
        '12m' => ['months' => 12, 'bucket' => 'month'],
        '3y' => ['months' => 36, 'bucket' => 'quarter'],
        '5y' => ['months' => 60, 'bucket' => 'quarter'],
        '10y' => ['months' => 120, 'bucket' => 'year'],
    ];

    public function edit(Request $request, Customer $customer, PanelManager $panel, DraftManager $drafts): Response
    {
        $customer->load('customerGroups:id,name');

        $staff = $panel->user();
        $draft = $staff ? $drafts->find($customer, $staff) : null;

        $addresses = $customer->addresses()->latest()->get()->map(fn (Address $address) => [
            'id' => $address->id,
            'title' => $address->title,
            'first_name' => $address->first_name,
            'last_name' => $address->last_name,
            'company_name' => $address->company_name,
            'tax_identifier' => $address->tax_identifier,
            'line_one' => $address->line_one,
            'line_two' => $address->line_two,
            'line_three' => $address->line_three,
            'city' => $address->city,
            'state' => $address->state,
            'postcode' => $address->postcode,
            'country_id' => $address->country_id,
            'delivery_instructions' => $address->delivery_instructions,
            'contact_email' => $address->contact_email,
            'contact_phone' => $address->contact_phone,
            'shipping_default' => $address->shipping_default,
            'billing_default' => $address->billing_default,
            'update_url' => route('panel.customers.addresses.update', [$customer, $address]),
            'destroy_url' => route('panel.customers.addresses.destroy', [$customer, $address]),
        ]);

        $usersRelation = $customer->users();
        $usersTable = $usersRelation->getRelated()->getTable();

        $users = $usersRelation->get(["{$usersTable}.id", "{$usersTable}.name", "{$usersTable}.email"])->map(fn ($user) => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'unlink_url' => route('panel.customers.users.destroy', ['customer' => $customer, 'user' => $user->id]),
        ]);

        $activities = $customer->activities()
            ->with('causer')
            ->latest()
            ->limit(25)
            ->get()
            ->map(fn (Activity $activity) => TimelineActivity::toArray($activity));

        $placedOrders = $customer->orders()->whereNotNull('placed_at');

        $orderCount = (clone $placedOrders)->count();

        // Lifetime spend reported in the default currency; per-order totals are
        // stored in the order's own currency, so divide by the exchange rate
        // captured when the order was placed.
        $totalSpend = (int) round((float) (clone $placedOrders)
            ->selectRaw('COALESCE(SUM(total / NULLIF(exchange_rate, 0)), 0) AS spend')
            ->value('spend'));

        $stats = [
            'orders' => $orderCount,
            'totalSpend' => null,
            'avgOrder' => null,
            'latestOrderAt' => (clone $placedOrders)->max('placed_at'),
        ];

        if ($orderCount && ($defaultCurrency = Currency::getDefault())) {
            $stats['totalSpend'] = (new PriceValue($totalSpend, $defaultCurrency))->format();
            $stats['avgOrder'] = (new PriceValue((int) round($totalSpend / $orderCount), $defaultCurrency))->format();
        }

        $orders = (clone $placedOrders)
            ->latest('placed_at')
            ->limit(25)
            ->get()
            ->map(fn (Order $order) => [
                'id' => $order->id,
                'reference' => $order->reference,
                'status' => $order->lifecycleStatus(),
                'status_label' => __('lunar::states.order.'.$order->lifecycleStatus()),
                'placed_at' => $order->placed_at,
                'total' => $order->format('total'),
            ]);

        return Inertia::render('customers/Edit', [
            'customer' => [
                'id' => $customer->id,
                'title' => $customer->title,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'company_name' => $customer->company_name,
                'tax_identifier' => $customer->tax_identifier,
                'account_ref' => $customer->account_ref,
                'admin_notes' => $customer->admin_notes,
                'created_at' => $customer->created_at,
                'customer_groups' => $customer->customerGroups->map(fn (CustomerGroup $group) => [
                    'id' => $group->id,
                    'name' => $group->name,
                ]),
            ],
            'customerGroups' => CustomerGroup::all(['id', 'name']),
            'countries' => Country::orderBy('name')->get(['id', 'name']),
            'addresses' => $addresses,
            'users' => $users,
            'activities' => $activities,
            'orders' => $orders,
            'stats' => $stats,
            // Deferred: the heaviest query on the page (order value bucketed over
            // up to 10 years). It loads in a follow-up request after first paint,
            // and the range switcher reloads it the same way (only: ['orderChart']).
            'orderChart' => Inertia::defer(fn () => $this->orderChart($request, $customer)),
            'draft' => $draft ? [
                'data' => $draft->data,
                'updated_at' => $draft->updated_at?->toJSON(),
            ] : null,
            'urls' => [
                'index' => route('panel.customers.index'),
                'activityLog' => route('panel.settings.activity-log.index', ['subject_type' => $customer->getMorphClass()]),
                'update' => route('panel.customers.update', $customer),
                'destroy' => route('panel.customers.destroy', $customer),
                'addressesStore' => route('panel.customers.addresses.store', $customer),
                'usersStore' => route('panel.customers.users.store', $customer),
                'notesUpdate' => route('panel.customers.notes.update', $customer),
                'draft' => route('panel.customers.draft.update', $customer),
                'draftCommit' => route('panel.customers.draft.commit', $customer),
            ],
        ]);
    }

    /**
     * Placed-order value bucketed over the requested chart range, zero-filled
     * and valued in the default currency — the same basis as the lifetime
     * stats, so the chart and the stats card always agree. Bucketing happens
     * in PHP: a single customer's order count is small, and it avoids
     * cross-database date-formatting SQL.
     *
     * @return array{range: string, buckets: array<int, array{label: string, value: float|int, display: string}>}
     */
    protected function orderChart(Request $request, Customer $customer): array
    {
        $range = $request->string('chart_range')->value();
        $range = array_key_exists($range, self::CHART_RANGES) ? $range : '12m';

        ['months' => $months, 'bucket' => $bucket] = self::CHART_RANGES[$range];

        $stepMonths = match ($bucket) {
            'month' => 1,
            'quarter' => 3,
            'year' => 12,
        };

        $start = match ($bucket) {
            'month' => now()->startOfMonth(),
            'quarter' => now()->firstOfQuarter(),
            'year' => now()->startOfYear(),
        };
        $start = $start->subMonths($months - $stepMonths);

        $bucketKey = fn (Carbon $date): string => match ($bucket) {
            'month' => $date->format('Y-m'),
            'quarter' => $date->year.'-'.$date->quarter,
            'year' => (string) $date->year,
        };

        $bucketLabel = fn (Carbon $date): string => match ($bucket) {
            'month' => $date->translatedFormat('M y'),
            'quarter' => 'Q'.$date->quarter.' '.$date->year,
            'year' => (string) $date->year,
        };

        $spendByBucket = $customer->orders()
            ->whereNotNull('placed_at')
            ->where('placed_at', '>=', $start)
            ->get(['placed_at', 'total', 'exchange_rate'])
            ->groupBy(fn (Order $order) => $bucketKey($order->placed_at))
            ->map(fn ($orders) => (int) round($orders->sum(
                fn (Order $order) => $order->total / ($order->exchange_rate ?: 1),
            )));

        $defaultCurrency = Currency::getDefault();

        $buckets = [];

        for ($date = $start->copy(); $date->lessThanOrEqualTo(now()); $date->addMonths($stepMonths)) {
            $minor = $spendByBucket[$bucketKey($date)] ?? 0;

            $buckets[] = [
                'label' => $bucketLabel($date),
                'value' => $defaultCurrency
                    ? round($minor / $defaultCurrency->factor, $defaultCurrency->decimal_places)
                    : $minor / 100,
                'display' => $defaultCurrency
                    ? (string) (new PriceValue($minor, $defaultCurrency))->format()
                    : (string) ($minor / 100),
            ];
        }

        return ['range' => $range, 'buckets' => $buckets];
    }

    public function update(CustomerRequest $request, Customer $customer, UpdatesCustomer $updatesCustomer): RedirectResponse
    {
        $updatesCustomer->execute(
            $customer,
            $request->customerAttributes(),
            $request->customerGroupIds(),
        );

        return back()->with('success', __('panel::customers.flash_updated'));
    }

    public function destroy(Customer $customer, DeletesCustomer $deletesCustomer): RedirectResponse
    {
        $deletesCustomer->execute($customer);

        return redirect()->route('panel.customers.index')->with('success', __('panel::customers.flash_deleted'));
    }
}
