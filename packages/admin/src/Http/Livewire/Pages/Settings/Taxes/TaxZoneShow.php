<?php

namespace Lunar\Hub\Http\Livewire\Pages\Settings\Taxes;

use Livewire\Component;
use Livewire\WithPagination;
use Lunar\Models\TaxZone;

class TaxZoneShow extends Component
{
    use WithPagination;

    public TaxZone $taxZone;

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.settings.taxes.tax-zones.show')
            ->layout('adminhub::layouts.settings', [
                'title' => $this->taxZone->name,
                'menu' => 'settings',
            ]);
    }
}
