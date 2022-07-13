<?php

namespace GetCandy\Hub\Http\Livewire\Components\Settings\Taxes;

use GetCandy\Models\TaxClass;

class TaxZoneShow extends AbstractTaxZone
{
    /**
     * {@inheritDoc}
     */
    public function mount()
    {
        $this->selectedCountries = $this->taxZone->countries->pluck('country_id')->toArray();

        $this->country = $this->taxZone->countries->pluck('country_id')->first();

        $this->selectedStates = $this->taxZone->states->pluck('state_id')->toArray();

        $this->postcodes = $this->taxZone->postcodes->pluck('postcode')->join("\n");

        $this->syncTaxRates();
    }

    /**
     * Save the TaxZone.
     *
     * @return void
     */
    public function save()
    {
        $this->taxZone->save();

        $this->saveDetails();

        $this->notify('Tax Zone updated');
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
