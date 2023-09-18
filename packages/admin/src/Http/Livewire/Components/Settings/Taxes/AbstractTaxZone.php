<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Taxes;

use Livewire\Component;
use Livewire\WithPagination;
use Lunar\Facades\DB;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Models\Country;
use Lunar\Models\CustomerGroup;
use Lunar\Models\State;
use Lunar\Models\TaxClass;
use Lunar\Models\TaxRate;
use Lunar\Models\TaxRateAmount;
use Lunar\Models\TaxZone;

abstract class AbstractTaxZone extends Component
{
    use Notifies, WithPagination;

    /**
     * The instance of the Tax Zone.
     */
    public TaxZone $taxZone;

    /**
     * The selected countries for the zone.
     */
    public array $selectedCountries = [];

    /**
     * The selected states for the zone.
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

    /**
     * The tax rates for the tax zone.
     */
    public array $taxRates = [];

    /**
     * The tax rate amounts for the tax zone.
     */
    public array $taxRateAmounts = [];

    /**
     * The linked customer groups.
     */
    public array $customerGroups = [];

    /**
     * {@inheritDoc}
     */
    public function rules()
    {
        return [
            'taxZone.name' => 'required',
            'taxZone.zone_type' => 'required',
            'taxZone.price_display' => 'required',
            'taxZone.active' => 'boolean|nullable',
            'taxZone.default' => 'boolean|nullable',
            'postcodes' => 'nullable|string|required_if:taxZone.zone_type,postcodes',
            'country' => 'nullable|string|required_if:taxZone.zone_type,postcodes,taxZone.zone_type,states',
            'selectedCountries' => 'array|required_if:taxZone.zone_type,country',
            'selectedStates' => 'array|required_if:taxZone.zone_type,states',
            'taxRates' => 'array',
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
    abstract public function mount();

    /**
     * Save the TaxZone.
     *
     * @return void
     */
    abstract public function save();

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

    /**
     * Return all the countries available in the system.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllCountriesProperty()
    {
        return Country::get();
    }

    /**
     * Return the filtered states available for selection.
     *
     * @return \Illuminate\Support\Collection
     */
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
     * Return all the tax classes in the system.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getTaxClassesProperty()
    {
        return TaxClass::get();
    }

    /**
     * Return all customer groups in the system.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllCustomerGroupsProperty()
    {
        return CustomerGroup::get();
    }

    /**
     * Unset a tax rate based on it's index.
     *
     * @param  int  $index
     * @return void
     */
    public function removeTaxRate($index)
    {
        unset($this->taxRates[$index]);
    }

    /**
     * Add a new tax rate to the array.
     *
     * @return void
     */
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

    /**
     * Sync tax rates based on any changes made.
     *
     * @return void
     */
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
     * Sync countries for the TaxZone.
     *
     * @param  array  $selectedStates
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
            $countriesToAssign->map(fn ($countryId) => [
                'country_id' => $countryId,
            ])
        );

        $this->taxZone->countries()
            ->whereNotIn('country_id', $selectedCountries)
            ->delete();
    }

    protected function syncCustomerGroups()
    {
        $this->customerGroups = $this->allCustomerGroups->map(function ($group) {
            $relation = $this->taxZone->customerGroups()->get()->first(function ($cg) use ($group) {
                return $cg->customer_group_id == $group->id;
            });

            return [
                'name' => $group->name,
                'customer_group_id' => $group->id,
                'link_id' => $relation?->id,
                'linked' => (bool) $relation,
            ];
        })->toArray();
    }

    /**
     * Sync states for the TaxZone.
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

            // First remove any existing rates that aren't in our list...
            // get the tax rates which have an ID.
            $ratesWithId = collect($this->taxRates)->pluck('id')->filter()->values();

            foreach ($this->taxZone->taxRates()->whereNotIn('id', $ratesWithId)->get() as $rate) {
                $rate->taxRateAmounts()->delete();
                $rate->delete();
            }

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

            // Customer groups to unlink.
            $unlinked = collect($this->customerGroups)->reject(function ($group) {
                return (bool) $group['linked'];
            });

            $linked = collect($this->customerGroups)->filter(function ($group) {
                return (bool) $group['linked'] && is_null($group['link_id']);
            });

            $this->taxZone->customerGroups()->whereIn('customer_group_id', $unlinked->pluck('customer_group_id'))->delete();

            if ($linked->count()) {
                $this->taxZone->customerGroups()->createMany(
                    $linked->map(fn ($group) => ['customer_group_id' => $group['customer_group_id']])
                );
            }

            $this->syncCustomerGroups();
        });

        $this->syncTaxRates();
    }
}
