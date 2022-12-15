<?php

namespace Lunar\Hub\Http\Livewire\Pages\Settings\CustomerGroups;

use Lunar\Models\CustomerGroup;
use Livewire\Component;

class CustomerGroupShow extends Component
{
    public CustomerGroup $customerGroup;

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.settings.customer-groups.show')
            ->layout('adminhub::layouts.settings', [
                'menu' => 'settings',
            ]);
    }
}
