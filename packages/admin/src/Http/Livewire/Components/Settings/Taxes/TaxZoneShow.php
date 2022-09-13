<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Taxes;

use Illuminate\Support\Facades\DB;
use Lunar\Models\TaxRateAmount;
use Lunar\Models\TaxZone;

class TaxZoneShow extends AbstractTaxZone
{
    /**
     * The ID of the tax zone we want to remove.
     *
     * @var int
     */
    public ?int $taxZoneToRemove = null;

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

        $this->taxZone->save();

        $this->saveDetails();

        $this->notify('Tax Zone updated');
    }

    public function deleteZone()
    {
        DB::transaction(function () {
            $taxZone = TaxZone::find($this->taxZoneToRemove);

            $taxZone->states()->delete();
            $taxZone->postcodes()->delete();
            $taxZone->customerGroups()->delete();
            $taxZone->countries()->delete();

            $taxRateIds = $taxZone->taxRates()->pluck('id');

            TaxRateAmount::whereIn('tax_rate_id', $taxRateIds)->delete();

            $taxZone->taxRates()->delete();

            $taxZone->delete();
        });

        return redirect()->route('hub.taxes.index');
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
