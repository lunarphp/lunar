<?php

namespace GetCandy\Hub\Http\Livewire\Pages\Settings\Staff;

use GetCandy\Hub\Models\Staff;
use Illuminate\Contracts\Auth\Authenticatable;
use Livewire\Component;

class StaffShow extends Component
{
    public Staff $staff;

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.settings.staff.show')
            ->layout('adminhub::layouts.settings', [
                'title' => 'Edit User',
            ]);
    }
}
