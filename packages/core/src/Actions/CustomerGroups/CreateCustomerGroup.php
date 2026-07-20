<?php

namespace Lunar\Core\Actions\CustomerGroups;

use Lunar\Core\Contracts\Actions\CustomerGroups\CreatesCustomerGroup;
use Lunar\Core\Models\CustomerGroup;

/**
 * Create a customer group, ensuring at most one group is ever marked default.
 */
class CreateCustomerGroup implements CreatesCustomerGroup
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): CustomerGroup
    {
        if ($attributes['default'] ?? false) {
            CustomerGroup::query()->where('default', true)->update(['default' => false]);
        }

        return CustomerGroup::create($attributes);
    }
}
