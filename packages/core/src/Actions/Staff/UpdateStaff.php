<?php

namespace Lunar\Core\Actions\Staff;

use Lunar\Core\Contracts\Actions\Staff\UpdatesStaff;
use Lunar\Core\Exceptions\StaffActionException;
use Lunar\Core\Models\Staff;

/**
 * Update a staff member. When supplied, the `roles` key replaces the staff
 * member's manifest roles. The last admin cannot lose the admin flag — a
 * store must always have someone who can manage it.
 */
class UpdateStaff implements UpdatesStaff
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(Staff $staff, array $attributes): Staff
    {
        $losingAdmin = $staff->admin
            && array_key_exists('admin', $attributes)
            && ! $attributes['admin'];

        if ($losingAdmin && ! Staff::query()->where('admin', true)->where('id', '!=', $staff->id)->exists()) {
            throw new StaffActionException('Cannot remove the admin flag from the last admin.');
        }

        $roles = array_key_exists('roles', $attributes) ? $attributes['roles'] : null;
        unset($attributes['roles']);

        if (($attributes['password'] ?? null) === null) {
            unset($attributes['password']);
        }

        $staff->update($attributes);

        if ($roles !== null) {
            $staff->syncRoles($roles);
        }

        return $staff;
    }
}
