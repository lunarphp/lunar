<?php

namespace Lunar\Core\Observers;

use Lunar\Core\Models\Address;

class AddressObserver
{
    /**
     * Handle the Address "creating" event.
     *
     * @return void
     */
    public function creating(Address $address)
    {
        $this->ensureOnlyOneDefaultShipping($address);
        $this->ensureOnlyOneDefaultBilling($address);
    }

    /**
     * Handle the Address "updating" event.
     *
     * @return void
     */
    public function updating(Address $address)
    {
        $this->ensureOnlyOneDefaultShipping($address);
        $this->ensureOnlyOneDefaultBilling($address);
    }

    /**
     * Ensures that only one default shipping address exists.
     *
     * @param  Address  $address  The address that will be saved.
     */
    protected function ensureOnlyOneDefaultShipping(Address $address): void
    {
        /** @var Address $address */
        if ($address->shipping_default) {
            $address = Address::query()
                ->whereCustomerId($address->customer_id)
                ->where('id', '!=', $address->id)
                ->whereShippingDefault(true)
                ->first();

            if ($address) {
                $address->shipping_default = false;
                $address->saveQuietly();
            }
        }
    }

    /**
     * Ensures that only one default billing address exists.
     *
     * @param  Address  $address  The address that will be saved.
     */
    protected function ensureOnlyOneDefaultBilling(Address $address): void
    {
        /** @var Address $address */
        if ($address->billing_default) {
            $address = Address::query()
                ->whereCustomerId($address->customer_id)
                ->where('id', '!=', $address->id)
                ->whereBillingDefault(true)
                ->first();

            if ($address) {
                $address->billing_default = false;
                $address->saveQuietly();
            }
        }
    }
}
