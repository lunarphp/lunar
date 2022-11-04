<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Taxes;

use Lunar\Models\TaxZone;

class TaxZoneCreate extends AbstractTaxZone
{
    /**
     * {@inheritDoc}
     */
    public function rules()
    {
        return [
            'taxZone.name' => 'required|unique:'.TaxZone::class.',name',
            'taxZone.zone_type' => 'required',
            'taxZone.price_display' => 'required',
            'taxZone.active' => 'boolean|nullable',
            'taxZone.default' => 'boolean|nullable',
            'postcodes' => 'nullable|string|required_if:taxZone.zone_type,postcodes',
            'country' => 'nullable|string|required_if:taxZone.zone_type,postcodes,taxZone.zone_type,states',
            'selectedCountries' => 'array|required_if:taxZone.zone_type,country',
            'selectedStates' => 'array|required_if:taxZone.zone_type,states',
            'taxRates' => 'array|min:1',
            'taxRates.*.priority' => 'required|numeric|min:1',
            'taxRates.*.name' => 'required|string',
            'taxRates.*.amounts.*.percentage' => 'numeric|min:0',
            'customerGroups' => 'array',
            'customerGroups.*.linked' => 'boolean|nullable',
        ];
    }

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

        $this->syncCustomerGroups();
    }

    /**
     * Save the TaxZone.
     *
     * @return void
     */
    public function save()
    {
        $this->validate();

        $this->taxZone->default = $this->taxZone->default ?: false;

        $this->taxZone->save();
//
        $this->saveDetails();

        $this->notify('Tax Zone created');

        return redirect()->route('hub.taxes.show', $this->taxZone->id);
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.settings.taxes.tax-zones.create')
            ->layout('adminhub::layouts.base');
    }
}
