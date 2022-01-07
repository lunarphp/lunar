<?php

namespace GetCandy\Hub\Http\Livewire\Components;

use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Hub\Models\Staff;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class Account extends Component
{
    use Notifies;

    /**
     * The staff account being edited.
     *
     * @var Staff
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
            'staff.lastname'  => 'nullable',
            'staff.email'     => 'email|required|unique:'.$this->staff->getTable().',email,'.$this->staff->id,
            'currentPassword' => 'nullable|current_password:staff',
            'password'        => 'nullable|min:8',
        ];
    }

    public function save()
    {
        $this->validate();

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
