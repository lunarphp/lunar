<?php

namespace Lunar\Core\Actions\Customers;

use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Contracts\Actions\Customers\LinksCustomerUser;
use Lunar\Core\Models\Customer;

/**
 * Link a storefront user to a customer, found by email against the
 * configured user provider model. Existing links are left untouched.
 */
class LinkCustomerUser implements LinksCustomerUser
{
    public function execute(Customer $customer, string $email): void
    {
        /** @var class-string<Model> $userModel */
        $userModel = config('auth.providers.users.model');

        $user = $userModel::where('email', $email)->firstOrFail();

        $customer->users()->syncWithoutDetaching([$user->getKey()]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($customer)
            ->event('user-linked')
            ->log('user-linked');
    }
}
