<?php

namespace Lunar\Panel\Http\Controllers\Customers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Models\Address;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;
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
            'contact_mail' => $address->contact_mail,
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

        return Inertia::render('customers/Edit', [
            'customer' => [
                'id' => $customer->id,
                'title' => $customer->title,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'company_name' => $customer->company_name,
                'tax_identifier' => $customer->tax_identifier,
                'account_ref' => $customer->account_ref,
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
            'urls' => [
                'index' => route('panel.customers.index'),
                'update' => route('panel.customers.update', $customer),
                'destroy' => route('panel.customers.destroy', $customer),
                'addressesStore' => route('panel.customers.addresses.store', $customer),
                'usersStore' => route('panel.customers.users.store', $customer),
            ],
        ]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'tax_identifier' => ['nullable', 'string', 'max:255'],
            'account_ref' => ['nullable', 'string', 'max:255'],
            'customer_group_ids' => ['nullable', 'array'],
            'customer_group_ids.*' => ['integer', Rule::exists((new CustomerGroup)->getTable(), 'id')],
        ]);

        $customer->update(collect($validated)->except('customer_group_ids')->all());

        $customer->customerGroups()->sync($validated['customer_group_ids'] ?? []);

        return back()->with('success', 'Customer updated.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return redirect()->route('panel.customers.index')->with('success', 'Customer deleted.');
    }
}
