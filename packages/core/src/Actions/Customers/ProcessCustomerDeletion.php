<?php

namespace Lunar\Actions\Customers;

use Lunar\Actions\AbstractAction;
use Lunar\Events\Customers\CustomerDeleted;
use Lunar\Events\Customers\CustomerDeleting;
use Lunar\Models\Customer;

class ProcessCustomerDeletion extends AbstractAction
{
    /**
     * Execute the action.
     */
    public function execute(Customer $customer, ?string $strategy = null): self
    {
        // Use provided strategy or fall back to config
        $strategy = $strategy ?: config('lunar.customers.deletion_strategy', 'anonymize');

        // Dispatch deleting event
        CustomerDeleting::dispatch($customer, $strategy);

        // Execute the appropriate strategy
        if ($strategy === 'delete') {
            DeleteCustomer::run($customer);
        } else {
            AnonymizeCustomer::run($customer);
        }

        // Set passthrough before dispatching final event
        $this->passThrough = $strategy === 'delete' ? true : $customer->fresh();

        // Dispatch deleted event
        CustomerDeleted::dispatch($strategy === 'delete' ? null : $customer, $strategy);

        return $this;
    }
}
