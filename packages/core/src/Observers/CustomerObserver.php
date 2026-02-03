<?php

namespace Lunar\Observers;

use Lunar\Models\Contracts\Customer as CustomerContract;

class CustomerObserver
{
    /**
     * Handle the Customer "deleting" event.
     *
     * This observer performs minimal cleanup to avoid FK constraint violations
     * when customers are deleted programmatically (e.g., $customer->delete()).
     * For intelligent deletion with configurable strategies, use ProcessCustomerDeletion action.
     */
    public function deleting(CustomerContract $customer): void
    {
        // Clean up carts to avoid FK constraints
        $customer->carts()->each(function ($cart) {
            $cart->lines()->delete();
            $cart->forceDelete();
        });

        // Detach many-to-many relationships to avoid FK constraints
        $customer->customerGroups()->detach();
        $customer->discounts()->detach();
        $customer->users()->detach();

        // Delete related records to avoid FK constraints
        $customer->addresses()->delete();

        // Delete wishlists if available
        if (method_exists($customer, 'wishlists')) {
            $customer->wishlists()->delete();
        }
    }
}
