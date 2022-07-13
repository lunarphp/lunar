<?php

namespace GetCandy\Hub\Http\Livewire\Components\Settings\Taxes;

use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Models\Country;
use GetCandy\Models\CustomerGroup;
use GetCandy\Models\State;
use GetCandy\Models\TaxClass;
use GetCandy\Models\TaxRate;
use GetCandy\Models\TaxZone;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

abstract class AbstractTaxZone extends Component
{
    use WithPagination, Notifies;

    /**
     * The instance of the Tax Zone.
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

    abstract public function mount();

    public function updatedRateId($val)
    {
        $this->taxRate = $val ? TaxRate::find($val) : null;

        if ($this->taxRate) {
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
    }

    /**
     * Save the TaxZone.
     *
     * @return void
     */
    abstract public function save();

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

    protected function syncCountries(array $selectedCountries)
    {
        $existingCountries = $this->taxZone->countries()->pluck('country_id');

        // Countries to assign
        $countriesToAssign = collect($selectedCountries)
            ->reject(function ($countryId) use ($existingCountries) {
                return $existingCountries->contains($countryId);
            });

        $this->taxZone->countries()->createMany(
            $countriesToAssign->map(fn ($countryId) => [
                'country_id' => $countryId,
            ])
        );

        $this->taxZone->countries()
            ->whereNotIn('country_id', $selectedCountries)
            ->delete();
    }

    protected function syncStates(array $selectedStates)
    {
        $existingStates = $this->taxZone->states()->pluck('state_id');

        // States to assign
        $statesToAssign = collect($selectedStates)
            ->reject(function ($stateId) use ($existingStates) {
                return $existingStates->contains($stateId);
            });

        $this->taxZone->states()->createMany(
            $statesToAssign->map(fn ($stateId) => [
                'state_id' => $stateId,
            ])
        );

        $this->taxZone->states()
            ->whereNotIn('state_id', $selectedStates)
            ->delete();
    }

    /**
     * Save common details across new and existing zones.
     *
     * @return void
     */
    public function saveDetails()
    {
        DB::transaction(function () {
            if ($this->taxZone->zone_type != 'country') {
                $this->taxZone->countries()->delete();
                $this->selectedCountries = [];
            } else {
                $this->syncCountries(
                    $this->selectedCountries
                );
            }

            if ($this->taxZone->zone_type != 'states') {
                $this->taxZone->states()->delete();
                $this->selectedStates = [];
            } else {
                $this->syncCountries([
                    $this->country,
                ]);

                $this->syncStates(
                    $this->selectedStates
                );
            }

            if ($this->taxZone->zone_type == 'postcodes') {
                $this->syncCountries(
                    [$this->country]
                );

                $postcodes = collect(
                    explode(
                        "\n",
                        str_replace(' ', '', $this->postcodes)
                    )
                )->unique()->filter();

                $existing = $this->taxZone->postcodes()->delete();

                $this->taxZone->postcodes()->createMany(
                    $postcodes->map(function ($postcode) {
                        return [
                            'country_id' => $this->country,
                            'postcode' => $postcode,
                        ];
                    })
                );

                $this->postcodes = $this->taxZone->postcodes()->pluck('postcode')->join("\n");
            } else {
                $this->taxZone->postcodes()->delete();
            }
        });
    }
}
