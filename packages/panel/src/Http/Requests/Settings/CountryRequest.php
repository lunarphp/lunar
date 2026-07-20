<?php

namespace Lunar\Panel\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Lunar\Core\Models\Country;

/** Used by the country update endpoint; countries are seeded, never created from the panel. */
class CountryRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var Country|null $country */
        $country = $this->route('country');

        return [
            'name' => ['required', 'string', 'max:255'],
            'iso2' => [
                'required', 'string', 'size:2', 'alpha',
                Rule::unique(Country::class, 'iso2')->ignore($country?->id),
            ],
            'iso3' => [
                'required', 'string', 'size:3', 'alpha',
                Rule::unique(Country::class, 'iso3')->ignore($country?->id),
            ],
        ];
    }

    /**
     * The validated input shaped for the country actions: ISO codes
     * normalised to uppercase.
     *
     * @return array<string, mixed>
     */
    public function countryAttributes(): array
    {
        $validated = $this->validated();

        return [
            'name' => $validated['name'],
            'iso2' => strtoupper($validated['iso2']),
            'iso3' => strtoupper($validated['iso3']),
        ];
    }
}
