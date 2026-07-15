<?php

namespace Lunar\Panel\Http\Controllers\Customers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Lunar\Core\Contracts\Actions\Customers\LinksCustomerUser;
use Lunar\Core\Contracts\Actions\Customers\UnlinksCustomerUser;
use Lunar\Core\Models\Customer;
use Lunar\Panel\Http\Requests\Customers\LinkCustomerUserRequest;

class CustomerUserController
{
    public function store(LinkCustomerUserRequest $request, Customer $customer, LinksCustomerUser $linksCustomerUser): RedirectResponse
    {
        try {
            $linksCustomerUser->execute($customer, $request->validated()['email']);
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
