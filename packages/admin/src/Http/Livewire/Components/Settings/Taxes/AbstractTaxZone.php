<?php

namespace GetCandy\Hub\Http\Livewire\Components\Settings\Taxes;

use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Models\Country;
use GetCandy\Models\CustomerGroup;
use GetCandy\Models\State;
use GetCandy\Models\TaxClass;
use GetCandy\Models\TaxRate;
use GetCandy\Models\TaxRateAmount;
use Illuminate\Support\Facades\DB;
use GetCandy\Models\TaxZone;
use Livewire\Component;
use Livewire\WithPagination;

abstract class AbstractTaxZone extends Component
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
     * The single country related to the zone.
     *
     * @var string
     */
    public ?string $country = null;

    public array $taxRates = [];

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
          'taxRates' => 'array',
          'taxRates.*.name' => 'string',
          // 'taxRateAmounts' => 'array',
          // 'taxRateAmounts.*.percentage' => 'numeric',
          // 'taxRateAmounts.*.tax_class_id' => 'required',
        ];
    }

    abstract public function mount();

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

    public function getTaxClassesProperty()
    {
        return TaxClass::get();
    }

    public function removeTaxRate($index)
    {
        unset($this->taxRates[$index]);
    }

    public function addTaxRate()
    {
        $this->taxRates[] = [
            'id' => null,
            'name' => null,
            'priority' => count($this->taxRates) + 1,
            'amounts' => $this->taxClasses->map(function ($taxClass) {
                return [
                    'id' => null,
                    'tax_class_id' => $taxClass->id,
                    'tax_class_name' => $taxClass->name,
                    'percentage' => 0,
                ];
            })->toArray(),
        ];
    }

    protected function syncTaxRates()
    {
        $this->taxRates = $this->taxZone->taxRates()->get()->map(function ($taxRate) {
            return [
                'id' => $taxRate->id,
                'name' => $taxRate->name,
                'priority' => $taxRate->priority,
                'amounts' => $this->taxClasses->map(function ($taxClass) use ($taxRate) {
                    $amount = $taxRate->taxRateAmounts->first(function ($amount) use ($taxClass) {
                        return $amount->tax_class_id == $taxClass->id;
                    });
                    return [
                        'id' => $amount?->id,
                        'tax_class_id' => $taxClass->id,
                        'tax_class_name' => $taxClass->name,
                        'percentage' => $amount->percentage ?? 0,
                    ];
                })->toArray(),
            ];
        })->toArray();
    }

    /**
     * Sync countries for the TaxZone
     *
     * @param array $selectedStates
     *
     * @return void
     */
    protected function syncCountries(array $selectedCountries)
    {
        $existingCountries = $this->taxZone->countries()->pluck('country_id');

        // Countries to assign
        $countriesToAssign = collect($selectedCountries)
            ->reject(function ($countryId) use ($existingCountries) {
                return $existingCountries->contains($countryId);
            });

        $this->taxZone->countries()->createMany(
            $countriesToAssign->map(fn($countryId) => [
                'country_id' => $countryId
            ])
        );

        $this->taxZone->countries()
            ->whereNotIn('country_id', $selectedCountries)
            ->delete();
    }

    /**
     * Sync states for the TaxZone
     *
     * @param array $selectedStates
     *
     * @return void
     */
    protected function syncStates(array $selectedStates)
    {
        $existingStates = $this->taxZone->states()->pluck('state_id');

        // States to assign
        $statesToAssign = collect($selectedStates)
            ->reject(function ($stateId) use ($existingStates) {
                return $existingStates->contains($stateId);
            });


        $this->taxZone->states()->createMany(
            $statesToAssign->map(fn($stateId) => [
                'state_id' => $stateId
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
                    $this->country
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

            // First remove any existing rates that aren't in our list...
            // get the tax rates which have an ID.
            $ratesWithId = collect($this->taxRates)->pluck('id')->filter()->values();
//
            foreach ($this->taxZone->taxRates()->whereNotIn('id', $ratesWithId)->get() as $rate) {
                $rate->taxRateAmounts()->delete();
                $rate->delete();
            }
//
//             dd(1);

            foreach ($this->taxRates as $taxRate) {
                if ($taxRate['id']) {
                    $taxRateModel = TaxRate::find($taxRate['id']);
                    $taxRateModel->update([
                        'name' => $taxRate['name'],
                        'priority' => $taxRate['priority'],
                    ]);
                } else {
                    $taxRateModel = TaxRate::create([
                        'name' => $taxRate['name'],
                        'priority' => $taxRate['priority'],
                        'tax_zone_id' => $this->taxZone->id,
                    ]);
                    $taxRateModel->save();
                }

                foreach ($taxRate['amounts'] as $amount) {
                    if ($amount['id']) {
                        $amountModel = TaxRateAmount::find($amount['id']);
                        $amountModel->update([
                            'percentage' => $amount['percentage'],
                        ]);
                    } else {
                        $amountModel = new TaxRateAmount([
                            'percentage' => $amount['percentage'],
                            'tax_class_id' => $amount['tax_class_id'],
                            'tax_rate_id' => $taxRateModel->id,
                        ]);
                        $amountModel->save();
                    }
                }
            }
        });

        $this->syncTaxRates();
    }
}
