<?php

namespace Lunar\Core\Contracts\Actions\CustomerGroups;

use Lunar\Core\Models\CustomerGroup;

interface DeletesCustomerGroup
{
    public function execute(CustomerGroup $customerGroup): void;
}
