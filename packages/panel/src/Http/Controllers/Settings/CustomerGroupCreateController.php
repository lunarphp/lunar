<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Lunar\Core\Contracts\Actions\CustomerGroups\CreatesCustomerGroup;
use Lunar\Panel\Http\Requests\Settings\CustomerGroupRequest;

class CustomerGroupCreateController
{
    public function store(CustomerGroupRequest $request, CreatesCustomerGroup $createsCustomerGroup): RedirectResponse
    {
        $createsCustomerGroup->execute($request->customerGroupAttributes());

        return redirect()->route('panel.settings.customer-groups.index')->with('success', __('panel::customer_groups.flash_created'));
    }
}
