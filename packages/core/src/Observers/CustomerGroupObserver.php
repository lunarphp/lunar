<?php

namespace Lunar\Observers;

use Lunar\Models\Contracts\CustomerGroup as CustomerGroupContract;
use Lunar\Models\CustomerGroup;

class CustomerGroupObserver
{
    /**
     * Handle the CustomerGroup "created" event.
     *
     * @return void
     */
    public function created(CustomerGroupContract $customerGroup)
    {
        $this->ensureOnlyOneDefault($customerGroup);
    }

    /**
     * Handle the CustomerGroup "updated" event.
     *
     * @return void
     */
    public function updated(CustomerGroupContract $customerGroup)
    {
        $this->ensureOnlyOneDefault($customerGroup);
    }

    /**
     * Handle the CustomerGroup "deleted" event.
     *
     * @return void
     */
    public function deleted(CustomerGroupContract $customerGroup)
    {
        //
    }

    /**
     * Handle the CustomerGroup "forceDeleted" event.
     *
     * @return void
     */
    public function forceDeleted(CustomerGroupContract $customerGroup)
    {
        //
    }

    /**
     * Ensures that only one default CustomerGroup exists.
     *
     * @param  CustomerGroupContract  $savedCustomerGroup  The customer group that was just saved.
     */
    protected function ensureOnlyOneDefault(CustomerGroupContract $savedCustomerGroup): void
    {
        // Wrap here so we avoid a query if it's not been set to default.
        if ($savedCustomerGroup->default) {
            CustomerGroup::modelClass()::withoutEvents(function () use ($savedCustomerGroup) {
                CustomerGroup::modelClass()::whereDefault(true)->where('id', '!=', $savedCustomerGroup->id)->update([
                    'default' => false,
                ]);
            });
        }
    }
}
