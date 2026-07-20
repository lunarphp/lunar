<?php

namespace Lunar\Core\Actions\TaxZones;

use Lunar\Core\Contracts\Actions\TaxZones\UpdatesTaxZone;
use Lunar\Core\Exceptions\TaxZoneActionException;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\TaxRate;
use Lunar\Core\Models\TaxZone;

/**
 * Update a tax zone and, when supplied, sync its coverage and rates in one
 * pass. The default flag moves by promoting another zone, never by
 * unsetting — the model's updated hook un-defaults whichever zone held it.
 *
 * Beyond the zone's own columns, the attributes may carry:
 * - `countries` — country ids replacing the zone's country coverage
 * - `states` — state ids replacing the zone's state coverage
 * - `postcodes` — `{country_id, postcode}` rows replacing the postcode coverage
 * - `customer_groups` — customer group ids replacing the group limits
 * - `rates` — `{id?, name, priority, amounts: {tax_class_id: percentage}}`
 *   rows replacing the zone's tax rates; rows with an id update in place so
 *   existing order lines keep their rate references
 */
class UpdateTaxZone implements UpdatesTaxZone
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(TaxZone $taxZone, array $attributes): TaxZone
    {
        if ($taxZone->default && array_key_exists('default', $attributes) && ! $attributes['default']) {
            throw new TaxZoneActionException('Cannot unset the default tax zone. Make another tax zone the default instead.');
        }

        $countries = $attributes['countries'] ?? null;
        $states = $attributes['states'] ?? null;
        $postcodes = $attributes['postcodes'] ?? null;
        $customerGroups = $attributes['customer_groups'] ?? null;
        $rates = $attributes['rates'] ?? null;

        unset($attributes['countries'], $attributes['states'], $attributes['postcodes'], $attributes['customer_groups'], $attributes['rates']);

        DB::transaction(function () use ($taxZone, $attributes, $countries, $states, $postcodes, $customerGroups, $rates): void {
            $taxZone->update($attributes);

            if ($countries !== null) {
                $taxZone->countries()->whereNotIn('country_id', $countries)->delete();
                $existing = $taxZone->countries()->pluck('country_id')->all();
                $taxZone->countries()->createMany(
                    collect($countries)->diff($existing)->map(fn ($id) => ['country_id' => $id])
                );
            }

            if ($states !== null) {
                $taxZone->states()->whereNotIn('state_id', $states)->delete();
                $existing = $taxZone->states()->pluck('state_id')->all();
                $taxZone->states()->createMany(
                    collect($states)->diff($existing)->map(fn ($id) => ['state_id' => $id])
                );
            }

            if ($postcodes !== null) {
                $keep = collect($postcodes)->map(fn ($row) => $row['country_id'].':'.$row['postcode']);

                $taxZone->postcodes()->get()
                    ->filter(fn ($row) => ! $keep->contains($row->country_id.':'.$row->postcode))
                    ->each->delete();

                $existing = $taxZone->postcodes()->get()->map(fn ($row) => $row->country_id.':'.$row->postcode);

                $taxZone->postcodes()->createMany(
                    collect($postcodes)
                        ->reject(fn ($row) => $existing->contains($row['country_id'].':'.$row['postcode']))
                        ->map(fn ($row) => ['country_id' => $row['country_id'], 'postcode' => $row['postcode']])
                );
            }

            if ($customerGroups !== null) {
                $taxZone->customerGroups()->whereNotIn('customer_group_id', $customerGroups)->delete();
                $existing = $taxZone->customerGroups()->pluck('customer_group_id')->all();
                $taxZone->customerGroups()->createMany(
                    collect($customerGroups)->diff($existing)->map(fn ($id) => ['customer_group_id' => $id])
                );
            }

            if ($rates !== null) {
                $this->syncRates($taxZone, $rates);
            }
        });

        return $taxZone;
    }

    /**
     * @param  array<int, array{id?: int|null, name: string, priority: int, amounts?: array<int|string, mixed>}>  $rates
     */
    protected function syncRates(TaxZone $taxZone, array $rates): void
    {
        $keepIds = collect($rates)->pluck('id')->filter();

        $taxZone->taxRates()->whereNotIn('id', $keepIds)->get()->each(function (TaxRate $rate): void {
            $rate->taxRateAmounts()->delete();
            $rate->delete();
        });

        foreach ($rates as $row) {
            /** @var TaxRate $rate */
            $rate = isset($row['id'])
                ? $taxZone->taxRates()->findOrFail($row['id'])
                : $taxZone->taxRates()->make();

            $rate->fill([
                'name' => $row['name'],
                'priority' => $row['priority'],
            ])->save();

            foreach ($row['amounts'] ?? [] as $taxClassId => $percentage) {
                $rate->taxRateAmounts()->updateOrCreate(
                    ['tax_class_id' => $taxClassId],
                    ['percentage' => $percentage],
                );
            }

            $rate->taxRateAmounts()->whereNotIn('tax_class_id', array_keys($row['amounts'] ?? []))->delete();
        }
    }
}
