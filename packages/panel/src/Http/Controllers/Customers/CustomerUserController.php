<?php

namespace Lunar\Panel\Http\Controllers\Customers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Lunar\Core\Models\Customer;

class CustomerUserController
{
    public function store(Request $request, Customer $customer): RedirectResponse
    {
        /** @var class-string<Model> $userModel */
        $userModel = config('auth.providers.users.model');

        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:'.(new $userModel)->getTable().',email'],
        ]);

        $user = $userModel::where('email', $validated['email'])->firstOrFail();

        $customer->users()->syncWithoutDetaching([$user->getKey()]);

        return back()->with('success', 'User linked.');
    }

    public function destroy(Customer $customer, int $user): RedirectResponse
    {
        $customer->users()->detach($user);

        return back()->with('success', 'User unlinked.');
    }
}
