<?php

namespace Lunar\Core\Contracts\Actions\CustomerGroups;

use Lunar\Core\Models\CustomerGroup;

interface UpdatesCustomerGroup
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(CustomerGroup $customerGroup, array $attributes): CustomerGroup;
}
