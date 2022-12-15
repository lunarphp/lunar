<?php

namespace Lunar\Observers;

use Lunar\Models\Address;

class AddressObserver
{
    /**
     * Handle the Address "creating" event.
     *
     * @param  \Lunar\Models\Address  $address
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
     * @param  \Lunar\Models\Address  $address
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
