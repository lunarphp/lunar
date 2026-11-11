<?php

namespace Lunar\Shipping\Actions\ShippingZones;

use Lunar\Core\Facades\DB;
use Lunar\Shipping\Contracts\Actions\ShippingZones\UpdatesShippingZone;
use Lunar\Shipping\Models\ShippingZone;

/**
 * Update a shipping zone and, when supplied, replace its coverage and
 * exclusion lists in one pass.
 *
 * Beyond the zone's own columns, the attributes may carry:
 * - `countries` — country ids replacing the zone's country coverage
 * - `states` — state ids replacing the zone's state coverage
 * - `postcodes` — postcode strings replacing the postcode coverage
 * - `exclusion_lists` — exclusion list ids replacing the attached lists
 *
 * An absent key leaves that collection untouched. Coverage the zone's type
 * does not read is cleared regardless, so a zone switched from postcodes to
 * countries does not keep stale postcodes that the resolver would ignore.
 * A state or postcode zone still carries its single parent country under
 * `countries`, as the Filament form has always stored it.
 */
class UpdateShippingZone implements UpdatesShippingZone
{
    public const TYPES = ['unrestricted', 'countries', 'states', 'postcodes'];

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(ShippingZone $shippingZone, array $attributes): ShippingZone
    {
        $countries = $attributes['countries'] ?? null;
        $states = $attributes['states'] ?? null;
        $postcodes = $attributes['postcodes'] ?? null;
        $exclusionLists = $attributes['exclusion_lists'] ?? null;

        unset($attributes['countries'], $attributes['states'], $attributes['postcodes'], $attributes['exclusion_lists']);

        DB::transaction(function () use ($shippingZone, $attributes, $countries, $states, $postcodes, $exclusionLists): void {
            $shippingZone->update($attributes);

            $type = $shippingZone->type;

            if ($type === 'unrestricted') {
                $shippingZone->countries()->detach();
            } elseif ($countries !== null) {
                $shippingZone->countries()->sync($countries);
            }

            if ($type !== 'states') {
                $shippingZone->states()->detach();
            } elseif ($states !== null) {
                $shippingZone->states()->sync($states);
            }

            if ($type !== 'postcodes') {
                $shippingZone->postcodes()->delete();
            } elseif ($postcodes !== null) {
                $this->syncPostcodes($shippingZone, $postcodes);
            }

            if ($exclusionLists !== null) {
                $shippingZone->shippingExclusions()->sync($exclusionLists);
            }
        });

        return $shippingZone;
    }

    /**
     * @param  array<int, string>  $postcodes
     */
    protected function syncPostcodes(ShippingZone $shippingZone, array $postcodes): void
    {
        $wanted = collect($postcodes)
            ->map(fn (string $postcode) => str_replace(' ', '', $postcode))
            ->filter()
            ->unique()
            ->values();

        $shippingZone->postcodes()->whereNotIn('postcode', $wanted)->delete();

        $existing = $shippingZone->postcodes()->pluck('postcode');

        $shippingZone->postcodes()->createMany(
            $wanted->diff($existing)->map(fn (string $postcode) => ['postcode' => $postcode])->all()
        );
    }
}
