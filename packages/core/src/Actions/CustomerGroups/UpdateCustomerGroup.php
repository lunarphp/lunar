<?php

namespace Lunar\Core\Actions\CustomerGroups;

use Lunar\Core\Contracts\Actions\CustomerGroups\UpdatesCustomerGroup;
use Lunar\Core\Exceptions\CustomerGroupActionException;
use Lunar\Core\Models\CustomerGroup;

/**
 * Update a customer group, ensuring at most one group is ever marked default.
 * The default flag moves by promoting another group, never by unsetting — so
 * a store with customer groups always has a default.
 */
class UpdateCustomerGroup implements UpdatesCustomerGroup
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(CustomerGroup $customerGroup, array $attributes): CustomerGroup
    {
        if ($customerGroup->default && array_key_exists('default', $attributes) && ! $attributes['default']) {
            throw new CustomerGroupActionException('Cannot unset the default customer group. Make another group the default instead.');
        }

        if ($attributes['default'] ?? false) {
            CustomerGroup::query()->where('default', true)->where('id', '!=', $customerGroup->id)->update(['default' => false]);
        }

        $customerGroup->update($attributes);

        return $customerGroup;
    }
}
