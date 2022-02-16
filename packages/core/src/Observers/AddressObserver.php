<?php

namespace GetCandy\Observers;

use GetCandy\Models\Address;

class AddressObserver
{
    /**
     * Handle the Address "creating" event.
     *
     * @param  \GetCandy\Models\Address  $address
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
     * @param  \GetCandy\Models\Address  $address
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
     * @param Address $address
     */
    protected function ensureOnlyOneDefaultShipping(Address $address): void
    {
        if ($address->shipping_default) {
            $address = Address::query()
                ->whereCustomerId($address->customer_id)
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
     * @param Address $address
     */
    protected function ensureOnlyOneDefaultBilling(Address $address): void
    {
        if ($address->billing_default) {
            $address = Address::query()
                ->whereCustomerId($address->customer_id)
                ->whereBillingDefault(true)
                ->first();

            if ($address) {
                $address->billing_default = false;
                $address->saveQuietly();
            }
        }
    }
}
