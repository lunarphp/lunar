<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Staff;

use Livewire\Component;

class StaffIndex extends Component
{
    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.settings.staff.index')
        ->layout('adminhub::layouts.base');
    }
}
