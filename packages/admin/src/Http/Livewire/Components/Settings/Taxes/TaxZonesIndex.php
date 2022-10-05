<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Taxes;

use Livewire\Component;
use Livewire\WithPagination;
use Lunar\Models\TaxZone;

class TaxZonesIndex extends Component
{
    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.settings.taxes.tax-zones.index')
        ->layout('adminhub::layouts.base');
    }
}
