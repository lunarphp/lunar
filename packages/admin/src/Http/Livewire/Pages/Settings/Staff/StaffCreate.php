<?php

namespace GetCandy\Hub\Http\Livewire\Pages\Settings\Staff;

use Livewire\Component;

class StaffCreate extends Component
{
    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.settings.staff.create')
            ->layout('adminhub::layouts.settings', [
                'title' => 'Create Staff Member',
            ]);
    }
}
