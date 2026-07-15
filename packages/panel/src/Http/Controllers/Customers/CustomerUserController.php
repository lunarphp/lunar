<?php

namespace Lunar\Panel\Http\Controllers\Customers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Lunar\Core\Contracts\Actions\Customers\LinksCustomerUser;
use Lunar\Core\Contracts\Actions\Customers\UnlinksCustomerUser;
use Lunar\Core\Models\Customer;

class CustomerUserController
{
    public function store(Request $request, Customer $customer, LinksCustomerUser $linksCustomerUser): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            $linksCustomerUser->execute($customer, $validated['email']);
        } catch (ModelNotFoundException) {
            throw ValidationException::withMessages([
                'email' => __('panel::customers.link_user_not_found'),
            ]);
        }

        return back()->with('success', __('panel::customers.flash_user_linked'));
    }

    public function destroy(Customer $customer, int $user, UnlinksCustomerUser $unlinksCustomerUser): RedirectResponse
    {
        $unlinksCustomerUser->execute($customer, $user);

        return back()->with('success', __('panel::customers.flash_user_unlinked'));
    }
}
