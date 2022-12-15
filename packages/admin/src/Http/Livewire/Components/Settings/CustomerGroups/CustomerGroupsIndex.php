<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\CustomerGroups;

use Lunar\Models\CustomerGroup;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerGroupsIndex extends Component
{
    use WithPagination;

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.settings.customer-groups.index', [
            'customerGroups' => CustomerGroup::paginate(5),
        ])->layout('adminhub::layouts.base');
    }
}
