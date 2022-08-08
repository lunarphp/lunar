<?php

namespace GetCandy\Hub\Http\Livewire\Pages\Settings\Taxes;

use GetCandy\Models\TaxZone;
use Livewire\Component;
use Livewire\WithPagination;

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
