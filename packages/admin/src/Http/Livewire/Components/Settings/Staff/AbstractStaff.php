<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Staff;

use Illuminate\Support\Collection;
use Lunar\Facades\DB;
use Livewire\Component;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Hub\Models\Staff;

abstract class AbstractStaff extends Component
{
    use Notifies;

    /**
     * The staff model for the staff member we want to show.
     *
     * @var \Lunar\Hub\Models\Staff
     */
    public Staff $staff;

    /**
     * The current staff assigned permissions.
     *
     * @var Collection
     */
    public Collection $staffPermissions;

    /**
     * The new password for the staff member.
     *
     * @var string
     */
    public $password;

    /**
     * The password confirmation for the staff member.
     *
     * @var string
     */
    public $password_confirmation;

    /**
     * Listener for when password is updated.
     *
     * @return void
     */
    public function updatedPassword()
    {
        $this->validateOnly('password');
    }

    /**
     * Listener for when password confirmation is updated.
     *
     * @return void
     */
    public function updatedPasswordConfirmation()
    {
        $this->validateOnly('password');
    }

    /**
     * Toggle whether the staff member is an admin.
     *
     * @return void
     */
    public function toggleAdmin()
    {
        $this->staff->admin = ! $this->staff->admin;
    }

    /**
     * Sync the set permissions with the staff member.
     *
     * @return void
     */
    protected function syncPermissions()
    {
        // Current user permissions
        $this->staff
            ->permissions()
            ->whereNotIn(
                'handle',
                $this->staffPermissions->toArray()
            )->delete();

        DB::transaction(function () {
            foreach ($this->staffPermissions as $permission) {
                $this->staff->permissions()->updateOrCreate([
                    'handle' => $permission,
                ]);
            }
        });
    }

    /**
     * Toggle a permission for a staff member.
     *
     * @param  string  $handle
     * @param  array  $children
     * @return void
     */
    public function togglePermission($handle, $children = [])
    {
        $index = $this->staffPermissions->search($handle);

        if ($index !== false) {
            $this->removePermission($handle);
            foreach ($children as $child) {
                $this->removePermission($child);
            }

            return;
        }

        $this->addPermission($handle);
    }

    /**
     * Add a permission to the staff member.
     *
     * @param  string  $handle
     * @return void
     */
    public function addPermission($handle)
    {
        if ($this->staffPermissions->contains($handle)) {
            return;
        }
        $this->staffPermissions->push($handle)->flatten();
    }

    /**
     * Remove a permission from a staff member.
     *
     * @param  string  $handle
     * @return void
     */
    public function removePermission($handle)
    {
        $index = $this->staffPermissions->search($handle);
        $this->staffPermissions->splice($index, 1);
    }
}
