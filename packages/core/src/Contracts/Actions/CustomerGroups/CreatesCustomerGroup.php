<?php

namespace Lunar\Core\Contracts\Actions\CustomerGroups;

use Lunar\Core\Models\CustomerGroup;

interface CreatesCustomerGroup
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): CustomerGroup;
}
