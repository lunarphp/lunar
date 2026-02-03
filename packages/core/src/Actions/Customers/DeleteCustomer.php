<?php

namespace Lunar\Actions\Customers;

use Illuminate\Support\Facades\DB;
use Lunar\Actions\AbstractAction;
use Lunar\Models\Cart;
use Lunar\Models\Customer;
use Lunar\Models\Order;

class DeleteCustomer extends AbstractAction
{
    /**
     * Execute the action.
     */
    public function execute(Customer $customer): self
    {
        DB::transaction(function () use ($customer) {
            // Handle all related data cleanup
            $this->deleteRelatedData($customer);
            $this->handleOrphanedUsers($customer);

            // Delete the customer without triggering observer events to avoid duplication
            // The observer does basic cleanup, but we've already done comprehensive cleanup
            $customer::withoutEvents(function () use ($customer) {
                $customer->forceDelete();
            });
        });

        $this->passThrough = true;

        return $this;
    }

    /**
     * Delete related data.
     */
    protected function deleteRelatedData(Customer $customer): void
    {
        // Delete carts and their lines
        $customer->carts()->each(function (Cart $cart) {
            $cart->lines()->delete();
            $cart->forceDelete();
        });

        // Handle orders based on configuration
        if (! config('lunar.customers.preserve_order_data', true)) {
            $customer->orders()->each(function (Order $order) {
                $order->lines()->delete();
                $order->transactions()->delete();
                $order->delete();
            });
        } else {
            // Even in delete mode, preserve orders by anonymizing
            $customer->orders()->update([
                'customer_id' => null,
                'user_id' => null,
            ]);
        }

        // Delete addresses
        $customer->addresses()->delete();

        // Detach many-to-many relationships
        $customer->customerGroups()->detach();
        $customer->discounts()->detach();

        // Delete wishlists if available
        if (method_exists($customer, 'wishlists')) {
            $customer->wishlists()->delete();
        }
    }

    /**
     * Handle orphaned users cleanup.
     */
    protected function handleOrphanedUsers(Customer $customer): void
    {
        if (! config('lunar.customers.delete_orphaned_users', true)) {
            $customer->users()->detach();

            return;
        }

        $customer->users->each(function ($user) use ($customer) {
            // Check if user is associated with other customers
            $hasOtherCustomers = Customer::whereHas('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('id', '!=', $customer->id)->exists();

            if (! $hasOtherCustomers) {
                // This user is only associated with this customer, delete it
                $user->delete();
            } else {
                // Just detach from this customer
                $customer->users()->detach($user->id);
            }
        });
    }
}
