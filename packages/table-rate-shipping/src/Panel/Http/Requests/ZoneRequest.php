<?php

namespace Lunar\Shipping\Panel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\State;
use Lunar\Shipping\Actions\ShippingZones\UpdateShippingZone;
use Lunar\Shipping\Models\ShippingExclusionList;

/**
 * Shared by the zone store and update endpoints. The store endpoint carries
 * the zone's own columns; the update endpoint may also carry the coverage
 * and exclusion-list collections, which replace what the zone had.
 */
class ZoneRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(UpdateShippingZone::TYPES)],

            'countries' => ['sometimes', 'array'],
            'countries.*' => [Rule::exists(Country::class, 'id')],
            'states' => ['sometimes', 'array'],
            'states.*' => [Rule::exists(State::class, 'id')],
            'postcodes' => ['sometimes', 'array'],
            'postcodes.*' => ['string', 'max:20'],
            'exclusion_lists' => ['sometimes', 'array'],
            'exclusion_lists.*' => [Rule::exists(ShippingExclusionList::class, 'id')],
        ];
    }

    /**
     * The validated input shaped for the zone actions: collections omitted
     * entirely when not supplied so an update leaves them untouched.
     *
     * @return array<string, mixed>
     */
    public function zoneAttributes(): array
    {
        $validated = $this->validated();

        $attributes = [
            'name' => $validated['name'],
            'type' => $validated['type'],
        ];

        foreach (['countries', 'states', 'postcodes', 'exclusion_lists'] as $collection) {
            if (array_key_exists($collection, $validated)) {
                $attributes[$collection] = $validated[$collection];
            }
        }

        return $attributes;
    }
}
