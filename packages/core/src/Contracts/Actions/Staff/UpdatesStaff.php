<?php

namespace Lunar\Core\Contracts\Actions\Staff;

use Lunar\Core\Models\Staff;

interface UpdatesStaff
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(Staff $staff, array $attributes): Staff;
}
