<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Staff;

use Illuminate\Support\Facades\Hash;
use Lunar\Hub\Auth\Manifest;
use Lunar\Hub\Models\Staff;

class StaffCreate extends AbstractStaff
{
    /**
     * Called when the component has been mounted.
     *
     * @return void
     */
    public function mount()
    {
        $this->staff = new Staff();
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
            'staff.email' => 'required|email|unique:'.get_class($this->staff).',email',
            'staff.firstname' => 'string|max:255',
            'staff.lastname' => 'string|max:255',
            'staff.admin' => 'nullable|boolean',
            'password' => 'required|min:8|max:255|confirmed',
            'password_confirmation' => 'string',
        ];
    }

    /**
     * Create the staff member.
     *
     * @return void
     */
    public function create()
    {
        $this->validate();

        $this->staff->password = Hash::make($this->password);
        $this->staff->admin = (bool) $this->staff->admin;

        $this->staff->save();

        $this->syncPermissions();

        $this->notify('Staff member added.', 'hub.staff.index');
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

        return view('adminhub::livewire.components.settings.staff.create', [
            'permissions' => $permissions->sortByDesc(fn ($permission) => (bool) $permission->firstParty),
        ])->layout('adminhub::layouts.base');
    }
}
