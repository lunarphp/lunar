<?php

namespace Lunar\Panel\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\State;
use Lunar\Core\Models\TaxClass;

/**
 * Shared by the tax zone store and update endpoints. The store endpoint only
 * carries the zone's own columns; the update endpoint may also carry the
 * coverage and rate collections, which replace what the zone had.
 */
class TaxZoneRequest extends FormRequest
{
    public const ZONE_TYPES = ['country', 'state', 'postcode'];

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'zone_type' => ['required', Rule::in(self::ZONE_TYPES)],
            'active' => ['sometimes', 'boolean'],
            'default' => ['sometimes', 'boolean'],

            'countries' => ['sometimes', 'array'],
            'countries.*' => [Rule::exists(Country::class, 'id')],
            'states' => ['sometimes', 'array'],
            'states.*' => [Rule::exists(State::class, 'id')],
            'postcodes' => ['sometimes', 'array'],
            'postcodes.*.country_id' => ['required', Rule::exists(Country::class, 'id')],
            'postcodes.*.postcode' => ['required', 'string', 'max:20'],
            'customer_groups' => ['sometimes', 'array'],
            'customer_groups.*' => [Rule::exists(CustomerGroup::class, 'id')],

            'rates' => ['sometimes', 'array'],
            'rates.*.id' => ['nullable', 'integer'],
            'rates.*.name' => ['required', 'string', 'max:255'],
            'rates.*.priority' => ['required', 'integer', 'between:1,255'],
            'rates.*.amounts' => ['sometimes', 'array'],
            'rates.*.amounts.*' => ['required', 'numeric', 'between:0,100'],
        ];
    }

    /**
     * Extra check that amount keys are real tax class ids; array keys cannot
     * be validated with a rule.
     */
    protected function passedValidation(): void
    {
        $classIds = TaxClass::query()->pluck('id');

        foreach ($this->validated()['rates'] ?? [] as $rate) {
            foreach (array_keys($rate['amounts'] ?? []) as $taxClassId) {
                abort_unless($classIds->contains((int) $taxClassId), 422);
            }
        }
    }

    /**
     * The validated input shaped for the tax zone actions: flags cast and,
     * like the collections, omitted entirely when not supplied so an update
     * leaves them untouched.
     *
     * @return array<string, mixed>
     */
    public function taxZoneAttributes(): array
    {
        $validated = $this->validated();

        $attributes = [
            'name' => $validated['name'],
            'zone_type' => $validated['zone_type'],
        ];

        foreach (['active', 'default'] as $flag) {
            if (array_key_exists($flag, $validated)) {
                $attributes[$flag] = (bool) $validated[$flag];
            }
        }

        foreach (['countries', 'states', 'postcodes', 'customer_groups', 'rates'] as $collection) {
            if (array_key_exists($collection, $validated)) {
                $attributes[$collection] = $validated[$collection];
            }
        }

        return $attributes;
    }
}
