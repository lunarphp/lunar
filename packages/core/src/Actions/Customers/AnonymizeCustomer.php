<?php

namespace Lunar\Actions\Customers;

use Illuminate\Support\Facades\DB;
use Lunar\Actions\AbstractAction;
use Lunar\Models\Address;
use Lunar\Models\Cart;
use Lunar\Models\Customer;
use Lunar\Models\Order;

class AnonymizeCustomer extends AbstractAction
{
    /**
     * Execute the action.
     */
    public function execute(Customer $customer): self
    {
        $this->passThrough = DB::transaction(function () use ($customer) {
            $this->anonymizeRelatedData($customer);
            $this->anonymizeCustomerData($customer);
            $this->handleOrphanedUsers($customer);

            return $customer->fresh();
        });

        return $this;
    }

    /**
     * Anonymize customer's personal data.
     */
    protected function anonymizeCustomerData(Customer $customer): void
    {
        $fields = config('lunar.customers.anonymization_fields', [
            'first_name' => 'Anonymous',
            'last_name' => 'Customer',
            'company_name' => null,
            'tax_identifier' => null,
            'account_ref' => null,
            'meta' => [],
            'attribute_data' => [],
        ]);

        $updateData = collect($fields)->map(function ($value) use ($customer) {
            if (is_string($value) && $value === ':id') {
                return "customer_{$customer->id}";
            }
            if (is_string($value) && str_contains($value, ':id')) {
                return str_replace(':id', "customer_{$customer->id}", $value);
            }

            return $value;
        })->all();

        // Add anonymization timestamp to meta
        $meta = $updateData['meta'] ?? [];
        if (is_array($meta)) {
            $meta['anonymized_at'] = now()->toIso8601String();
            $meta['anonymized'] = true;
            $updateData['meta'] = $meta;
        }

        $customer->update($updateData);
    }

    /**
     * Anonymize related data.
     */
    protected function anonymizeRelatedData(Customer $customer): void
    {
        // Handle carts
        $cartHandling = config('lunar.customers.cart_handling', 'delete');
        if ($cartHandling === 'delete') {
            $customer->carts()->each(function (Cart $cart) {
                $cart->lines()->delete();
                $cart->forceDelete();
            });
        } else {
            $customer->carts()->update(['customer_id' => null]);
        }

        // Handle orders
        if (config('lunar.customers.preserve_order_data', true)) {
            $customer->orders()->each(function (Order $order) {
                $order->update([
                    'customer_id' => null,
                    'user_id' => null,
                ]);

                // Anonymize order addresses
                if ($order->shippingAddress) {
                    $this->anonymizeAddress($order->shippingAddress);
                }
                if ($order->billingAddress) {
                    $this->anonymizeAddress($order->billingAddress);
                }
            });
        }

        // Handle customer addresses
        $customer->addresses()->each(function (Address $address) {
            $this->anonymizeAddress($address);
        });

        // Detach relationships
        $customer->customerGroups()->detach();
        $customer->discounts()->detach();

        // Handle wishlists if available
        if (method_exists($customer, 'wishlists')) {
            $customer->wishlists()->delete();
        }
    }

    /**
     * Anonymize an address record.
     */
    protected function anonymizeAddress(Address $address): void
    {
        $address->update([
            'first_name' => 'Anonymous',
            'last_name' => 'Customer',
            'company_name' => null,
            'line_one' => 'Anonymized',
            'line_two' => null,
            'line_three' => null,
            'city' => 'Anonymized',
            'state' => null,
            'postcode' => '00000',
            'delivery_instructions' => null,
            'contact_email' => null,
            'contact_phone' => null,
            'meta' => [
                'anonymized_at' => now()->toIso8601String(),
                'anonymized' => true,
            ],
        ]);
    }

    /**
     * Handle orphaned users.
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
                $user->delete();
            } else {
                // Just detach from this customer
                $customer->users()->detach($user->id);
            }
        });
    }
}
