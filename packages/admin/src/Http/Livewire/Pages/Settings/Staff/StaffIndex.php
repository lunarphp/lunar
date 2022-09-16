<?php

namespace Lunar\Hub\Http\Livewire\Pages\Settings\Staff;

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
        return view('adminhub::livewire.pages.settings.staff.index')
            ->layout('adminhub::layouts.settings', [
                'menu' => 'settings',
            ]);
    }
}
