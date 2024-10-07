<?php

namespace Lunar\Observers;

use Lunar\Models\Address;
use Lunar\Models\Contracts\Address as AddressContract;

class AddressObserver
{
    /**
     * Handle the Address "creating" event.
     *
     * @return void
     */
    public function creating(AddressContract $address)
    {
        $this->ensureOnlyOneDefaultShipping($address);
        $this->ensureOnlyOneDefaultBilling($address);
    }

    /**
     * Handle the Address "updating" event.
     *
     * @return void
     */
    public function updating(AddressContract $address)
    {
        $this->ensureOnlyOneDefaultShipping($address);
        $this->ensureOnlyOneDefaultBilling($address);
    }

    /**
     * Ensures that only one default shipping address exists.
     *
     * @param  AddressContract  $address  The address that will be saved.
     */
    protected function ensureOnlyOneDefaultShipping(AddressContract $address): void
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
     * @param  AddressContract  $address  The address that will be saved.
     */
    protected function ensureOnlyOneDefaultBilling(AddressContract $address): void
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
