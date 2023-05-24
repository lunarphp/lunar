<?php

namespace Lunar\Hub\Http\Livewire\Components;

use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Hub\Models\Staff;

class Account extends Component
{
    use Notifies;

    /**
     * The staff account being edited.
     */
    public Staff $staff;

    /**
     * The current password.
     *
     * @var string
     */
    public $currentPassword;

    /**
     * The new password.
     *
     * @var string
     */
    public $password;

    /**
     * {@inheritDoc}
     */
    public function rules()
    {
        return [
            'staff.firstname' => 'required|string',
            'staff.lastname' => 'nullable',
            'staff.email' => 'email|required|unique:'.get_class($this->staff).',email,'.$this->staff->id,
            'currentPassword' => 'nullable|current_password:staff',
            'password' => 'nullable|min:8',
        ];
    }

    public function save()
    {
        $this->validate();

        if ($this->staff->isDirty(['email'])) {
            $this->emit('hub.staff.avatar.updated', $this->staff->gravatar);
        }

        if ($this->staff->isDirty(['firstname', 'lastname'])) {
            $this->emit('hub.staff.name.updated', $this->staff->fullName);
        }

        if ($this->password) {
            $this->staff->password = Hash::make($this->password);

            $this->password = null;
            $this->currentPassword = null;
        }

        $this->staff->save();

        $this->notify(
            __('adminhub::notifications.account.updated')
        );
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.account')
            ->layout('adminhub::layouts.base');
    }
}
