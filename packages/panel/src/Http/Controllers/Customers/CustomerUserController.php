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
                'email' => 'No user was found with that email address.',
            ]);
        }

        return back()->with('success', 'User linked.');
    }

    public function destroy(Customer $customer, int $user, UnlinksCustomerUser $unlinksCustomerUser): RedirectResponse
    {
        $unlinksCustomerUser->execute($customer, $user);

        return back()->with('success', 'User unlinked.');
    }
}
