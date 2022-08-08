<?php

namespace GetCandy\Hub\Http\Livewire\Components\Settings\Taxes;

use GetCandy\Models\TaxZone;
use Livewire\Component;
use Livewire\WithPagination;

class TaxZonesIndex extends Component
{
    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.settings.taxes.tax-zones.index')->layout('adminhub::layouts.base');
    }
}
