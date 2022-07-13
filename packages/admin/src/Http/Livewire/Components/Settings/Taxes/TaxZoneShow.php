<?php

namespace GetCandy\Hub\Http\Livewire\Components\Settings\Taxes;


class TaxZoneShow extends AbstractTaxZone
{
    public function mount()
    {
        $this->selectedCountries = $this->taxZone->countries->pluck('country_id')->toArray();

        $this->country = $this->taxZone->countries->pluck('country_id')->first();

        $this->selectedStates = $this->taxZone->states->pluck('state_id')->toArray();

        $this->postcodes = $this->taxZone->postcodes->pluck('postcode')->join("\n");
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.settings.taxes.tax-zones.show')
            ->layout('adminhub::layouts.base');
    }
}
