<?php

namespace Lunar\Core\Contracts\Actions\Staff;

use Lunar\Core\Models\Staff;

interface DeletesStaff
{
    public function execute(Staff $staff): void;
}
