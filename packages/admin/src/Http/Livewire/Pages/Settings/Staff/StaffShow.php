<?php

namespace Lunar\Hub\Http\Livewire\Pages\Settings\Staff;

use Livewire\Component;
use Lunar\Hub\Models\Staff;

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
                'menu' => 'settings',
            ]);
    }
}
