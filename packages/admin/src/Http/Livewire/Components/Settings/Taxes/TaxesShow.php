<?php

namespace GetCandy\Hub\Http\Livewire\Components\Settings\Taxes;

use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Models\Country;
use GetCandy\Models\CustomerGroup;
use GetCandy\Models\State;
use GetCandy\Models\TaxClass;
use GetCandy\Models\TaxRate;
use GetCandy\Models\TaxZone;
use Livewire\Component;
use Livewire\WithPagination;

class TaxesShow extends Component
{
    use WithPagination, Notifies;

    /**
     * The instance of the Tax Zone
     *
     * @var TaxZone
     */
    public TaxZone $taxZone;

    /**
     * The selected countries for the zone.
     *
     * @var array
     */
    public array $selectedCountries = [];

    /**
     * The selected states for the zone.
     *
     * @var array
     */
    public array $selectedStates = [];

    /**
     * The postcodes to associate to the zone.
     *
     * @var string
     */
    public ?string $postcodes = '';

    /**
     * Search term for filtering out countries/states.
     *
     * @var string
     */
    public ?string $searchTerm = null;

    /**
     * The ID of the tax rate to edit.
     *
     * @var int
     */
    public ?int $rateId = null;

    /**
     * The instance of the TaxRate to edit.
     *
     * @var TaxRate|null
     */
    public ?TaxRate $taxRate = null;

    /**
     * The single country related to the zone.
     *
     * @var string
     */
    public ?string $country = null;

    public array $taxRateAmounts = [];

    /**
     * {@inheritDoc}
     */
    public function rules()
    {
        return [
          'taxZone.name' => 'required',
          'taxZone.zone_type' => 'required',
          'postcodes' => 'nullable|string|required_if:taxZone.zone_type,postcodes',
          'country' => 'nullable|string|required_if:taxZone.zone_type,postcodes,taxZone.zone_type,states',
          'selectedCountries' => 'array|required_if:taxZone.zone_type,countries',
          'selectedStates' => 'array|required_if:taxZone.zone_type,states',
          'taxRate.name' => 'string',
          'taxRateAmounts' => 'array',
          'taxRateAmounts.*.percentage' => 'numeric',
          'taxRateAmounts.*.tax_class_id' => 'required',
        ];
    }

    public function mount()
    {
        $this->selectedCountries = $this->taxZone->countries->pluck('country_id')->toArray();

        $this->country = $this->taxZone->countries->pluck('country_id')->first();

        $this->selectedStates = $this->taxZone->states->pluck('state_id')->toArray();

        $this->postcodes = $this->taxZone->postcodes->pluck('postcode')->join("\n");
    }

    public function updatedRateId($val)
    {
        $this->taxRate = $val ? TaxRate::find($val) : null;

        $taxRateAmounts = $this->taxRate->taxRateAmounts;

        $this->taxRateAmounts = TaxClass::get()->map(function ($taxClass) use ($taxRateAmounts) {
            $taxRateAmount = $taxRateAmounts->first(fn ($rate) => $rate->tax_class_id == $taxClass->id);

            return [
                'name' => $taxClass->name,
                'tax_class_id' => $taxClass->id,
                'percentage' => $taxRateAmount?->percentage,
            ];
        })->toArray();
    }

    /**
     * Save the ShippingZone.
     *
     * @return void
     */
    public function save()
    {
        $this->taxZone->save();

        // $this->saveDetails();

        $this->notify('Shipping Zone updated');
    }


    public function getCustomerGroupsProperty()
    {
        return CustomerGroup::get();
    }

    /**
     * Return a list of available countries.
     *
     * @return Collection
     */
    public function getCountriesProperty()
    {
        return Country::where('name', 'LIKE', "%{$this->searchTerm}%")
            ->whereNotIn('id', $this->selectedCountries)->get();
    }

    public function getAllCountriesProperty()
    {
        return Country::get();
    }

    public function getStatesProperty()
    {
        return State::where('name', 'LIKE', "%{$this->searchTerm}%")
            ->whereNotIn('id', $this->selectedStates)
            ->whereCountryId($this->country)->get();
    }

    /**
     * Return a list of countries related to the zone.
     *
     * @return Collection
     */
    public function getZoneCountriesProperty()
    {
        return Country::whereIn('id', $this->selectedCountries)->get();
    }

    /**
     * Return a list of states related to the zone.
     *
     * @return Collection
     */
    public function getZoneStatesProperty()
    {
        return State::whereIn('id', $this->selectedStates)->get();
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.settings.taxes.show')
            ->layout('adminhub::layouts.base');
    }
}
