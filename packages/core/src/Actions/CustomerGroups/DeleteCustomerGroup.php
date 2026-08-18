<?php

namespace Lunar\Core\Actions\CustomerGroups;

use Lunar\Core\Contracts\Actions\CustomerGroups\DeletesCustomerGroup;
use Lunar\Core\Exceptions\CustomerGroupActionException;
use Lunar\Core\Models\CustomerGroup;

/**
 * Delete a customer group. Groups with customers are kept — move the
 * customers first — so no customer silently loses their group pricing and
 * discounts. The default group is also kept: make another group the default
 * first.
 */
class DeleteCustomerGroup implements DeletesCustomerGroup
{
    public function execute(CustomerGroup $customerGroup): void
    {
        if ($customerGroup->default) {
            throw new CustomerGroupActionException('Cannot delete the default customer group. Make another group the default first.');
        }

        if ($customerGroup->customers()->exists()) {
            throw new CustomerGroupActionException('Cannot delete a customer group with customers.');
        }

        $customerGroup->delete();
    }
}
