<?php

namespace Lunar\Core\Actions\Staff;

use Lunar\Core\Contracts\Actions\Staff\DeletesStaff;
use Lunar\Core\Exceptions\StaffActionException;
use Lunar\Core\Models\Staff;

/**
 * Delete (soft) a staff member. The last admin is kept — a store must always
 * have someone who can manage it.
 */
class DeleteStaff implements DeletesStaff
{
    public function execute(Staff $staff): void
    {
        if ($staff->admin && ! Staff::query()->where('admin', true)->where('id', '!=', $staff->id)->exists()) {
            throw new StaffActionException('Cannot delete the last admin.');
        }

        $staff->delete();
    }
}
