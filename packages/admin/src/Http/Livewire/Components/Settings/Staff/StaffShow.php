<?php

namespace GetCandy\Hub\Http\Livewire\Components\Settings\Staff;

use GetCandy\Hub\Auth\Manifest;
use GetCandy\Hub\Http\Livewire\Traits\ConfirmsDelete;
use GetCandy\Hub\Models\Staff;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class StaffShow extends AbstractStaff
{
    use ConfirmsDelete;

    /**
     * Called when the component has been mounted.
     *
     * @return void
     */
    public function mount()
    {
        $this->staffPermissions = $this->staff->permissions->pluck('handle');
    }

    /**
     * Define the validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'staffPermissions' => 'array',
            'staff.email' => 'required|email|unique:'.$this->staff->getTable().',email,'.$this->staff->id,
            'staff.firstname' => 'string|max:255',
            'staff.lastname' => 'string|max:255',
            'staff.admin' => 'nullable|boolean',
            'password' => 'nullable|min:8|max:255|confirmed',
        ];
    }

    /**
     * Delete a staff member.
     *
     * @return void
     */
    public function delete()
    {
        $this->staff->delete();
        $this->notify('Staff member was removed', 'hub.staff.index');
    }

    /**
     * Returns whether we have met the criteria to allow deletion.
     *
     * @return bool
     */
    public function getCanDeleteProperty()
    {
        return $this->deleteConfirm === $this->staff->email;
    }

    /**
     * Computed property to determine if we're editing ourself.
     *
     * @return bool
     */
    public function getOwnAccountProperty()
    {
        return $this->staff->id == Auth::user()->id;
    }

    /**
     * Update the staff member.
     *
     * @return void
     */
    public function update()
    {
        $this->validate();

        // If we only have one admin, we can't remove it.
        if (! $this->staff->admin && ! Staff::where('id', '!=', $this->staff->id)->whereAdmin(true)->exists()) {
            $this->notify('You must have at least one admin');

            return;
        }

        if ($this->password) {
            $this->staff->password = Hash::make($this->password);
        }

        $this->staff->save();

        $this->syncPermissions();

        $this->notify('Staff member updated');
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render(Manifest $manifest)
    {
        // dd($this);
        $permissions = $manifest->getGroupedPermissions();

        return view('adminhub::livewire.components.settings.staff.show', [
            'firstPartyPermissions' => $permissions->filter(fn ($permission) => (bool) $permission->firstParty),
        ])->layout('adminhub::layouts.base');
    }
}
