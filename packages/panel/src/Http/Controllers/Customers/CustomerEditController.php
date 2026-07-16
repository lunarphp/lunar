<?php

namespace Lunar\Panel\Http\Controllers\Customers;

use Illuminate\Http\RedirectResponse;
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
use Lunar\Panel\Http\Requests\Customers\CustomerRequest;
use Spatie\Activitylog\Models\Activity;

class CustomerEditController
{
    public function edit(Customer $customer): Response
    {
        $customer->load('customerGroups:id,name');

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
            ->map(fn (Activity $activity) => [
                'description' => $activity->description,
                'created_at' => $activity->created_at,
                'causer_name' => $activity->causer?->full_name ?? $activity->causer?->name ?? null,
            ]);

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
            'urls' => [
                'index' => route('panel.customers.index'),
                'update' => route('panel.customers.update', $customer),
                'destroy' => route('panel.customers.destroy', $customer),
                'addressesStore' => route('panel.customers.addresses.store', $customer),
                'usersStore' => route('panel.customers.users.store', $customer),
                'notesUpdate' => route('panel.customers.notes.update', $customer),
            ],
        ]);
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
