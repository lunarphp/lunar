<?php

namespace Lunar\Core\Actions\Staff;

use Lunar\Core\Contracts\Actions\Staff\CreatesStaff;
use Lunar\Core\Models\Staff;

/**
 * Create a staff member. The `roles` key assigns manifest roles by handle.
 */
class CreateStaff implements CreatesStaff
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): Staff
    {
        $roles = $attributes['roles'] ?? null;
        unset($attributes['roles']);

        /** @var Staff $staff */
        $staff = Staff::create($attributes);

        if ($roles !== null) {
            $staff->syncRoles($roles);
        }

        return $staff;
    }
}
