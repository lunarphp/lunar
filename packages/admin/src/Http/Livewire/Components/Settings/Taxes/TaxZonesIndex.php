<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Taxes;

use Lunar\Models\TaxZone;
use Livewire\Component;
use Livewire\WithPagination;

class TaxZonesIndex extends Component
{
    use WithPagination;

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.settings.taxes.tax-zones.index', [
            'taxZones' => TaxZone::paginate(),
        ])->layout('adminhub::layouts.base');
    }
}
