<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Contracts\Actions\CustomerGroups\DeletesCustomerGroup;
use Lunar\Core\Contracts\Actions\CustomerGroups\UpdatesCustomerGroup;
use Lunar\Core\Exceptions\CustomerGroupActionException;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Panel\Http\Requests\Settings\CustomerGroupRequest;

class CustomerGroupEditController
{
    public function edit(CustomerGroup $customerGroup): Response
    {
        return Inertia::render('settings/customer-groups/Edit', [
            'customerGroup' => [
                'id' => $customerGroup->id,
                'name' => $customerGroup->name,
                'handle' => $customerGroup->handle,
                'default' => $customerGroup->default,
            ],
            'hasCustomers' => $customerGroup->customers()->exists(),
            'urls' => [
                'update' => route('panel.settings.customer-groups.update', $customerGroup),
                'destroy' => route('panel.settings.customer-groups.destroy', $customerGroup),
                'index' => route('panel.settings.customer-groups.index'),
            ],
        ]);
    }

    public function update(CustomerGroupRequest $request, CustomerGroup $customerGroup, UpdatesCustomerGroup $updatesCustomerGroup): RedirectResponse
    {
        try {
            $updatesCustomerGroup->execute($customerGroup, $request->customerGroupAttributes());
        } catch (CustomerGroupActionException) {
            return back()->with('error', __('panel::customer_groups.default_unset_blocked'));
        }

        return redirect()->route('panel.settings.customer-groups.index')->with('success', __('panel::customer_groups.flash_updated'));
    }

    public function destroy(CustomerGroup $customerGroup, DeletesCustomerGroup $deletesCustomerGroup): RedirectResponse
    {
        try {
            $deletesCustomerGroup->execute($customerGroup);
        } catch (CustomerGroupActionException) {
            return back()->with('error', $customerGroup->default
                ? __('panel::customer_groups.delete_blocked_default')
                : __('panel::customer_groups.delete_blocked'));
        }

        return redirect()->route('panel.settings.customer-groups.index')->with('success', __('panel::customer_groups.flash_deleted'));
    }
}
