<?php

namespace Lunar\Hub\Http\Livewire\Pages\Settings\Taxes;

use Livewire\Component;
use Livewire\WithPagination;
use Lunar\Models\TaxZone;

class TaxZoneCreate extends Component
{
    use WithPagination;

    public TaxZone $taxZone;

    public function mount()
    {
        $this->taxZone = new TaxZone([
            'zone_type' => 'country',
            'price_display' => 'include_tax',
            'active' => true,
        ]);
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.settings.taxes.tax-zones.create')
            ->layout('adminhub::layouts.settings', [
                'title' => __('adminhub::settings.taxes.tax-zones.create_title'),
                'menu' => 'settings',
            ]);
    }
}
