<?php

namespace Lunar\Observers;

use Lunar\Models\CustomerGroup;

class CustomerGroupObserver
{
    /**
     * Handle the Language "created" event.
     *
     * @return void
     */
    public function created(CustomerGroup $customerGroup)
    {
        $this->ensureOnlyOneDefault($customerGroup);
    }

    /**
     * Handle the CustomerGroup "updated" event.
     *
     * @return void
     */
    public function updated(CustomerGroup $customerGroup)
    {
        $this->ensureOnlyOneDefault($customerGroup);
    }

    /**
     * Handle the CustomerGroup "deleted" event.
     *
     * @return void
     */
    public function deleted(CustomerGroup $customerGroup)
    {
        //
    }

    /**
     * Handle the CustomerGroup "forceDeleted" event.
     *
     * @return void
     */
    public function forceDeleted(CustomerGroup $customerGroup)
    {
        //
    }

    /**
     * Ensures that only one default CustomerGroup exists.
     *
     * @param  \Lunar\Models\CustomerGroup  $savedCustomerGroup  The customer group that was just saved.
     */
    protected function ensureOnlyOneDefault(CustomerGroup $savedCustomerGroup): void
    {
        // Wrap here so we avoid a query if it's not been set to default.
        if ($savedCustomerGroup->default) {
            CustomerGroup::withoutEvents(function () use ($savedCustomerGroup) {
                CustomerGroup::whereDefault(true)->where('id', '!=', $savedCustomerGroup->id)->update([
                    'default' => false,
                ]);
            });
        }
    }
}
