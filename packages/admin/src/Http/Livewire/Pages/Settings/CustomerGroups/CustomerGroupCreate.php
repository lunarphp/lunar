<?php

namespace GetCandy\Hub\Http\Livewire\Pages\Settings\CustomerGroups;

use Livewire\Component;

class CustomerGroupCreate extends Component
{
    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.settings.customer-groups.create')
            ->layout('adminhub::layouts.settings', [
                'menu' => 'settings',
            ]);
    }
}
