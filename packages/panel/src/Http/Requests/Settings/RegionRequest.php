<?php

namespace Lunar\Panel\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Region;
use Lunar\Core\Models\TaxZone;

/** Shared by the region store and update endpoints, whose rules are identical bar the handle unique scope. */
class RegionRequest extends FormRequest
{
    /** Handles are stored slugged, so normalise first and validate the stored form. */
    protected function prepareForValidation(): void
    {
        $handle = is_string($this->input('handle')) ? $this->input('handle') : '';
        $name = is_string($this->input('name')) ? $this->input('name') : '';

        $this->merge(['handle' => Str::slug($handle) ?: (Str::slug($name) ?: null)]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var Region|null $region */
        $region = $this->route('region');

        return [
            'name' => ['required', 'string', 'max:255'],
            'handle' => [
                'nullable', 'string', 'max:255',
                Rule::unique(Region::class, 'handle')->ignore($region?->id),
            ],
            'channel_id' => ['required', Rule::exists(Channel::class, 'id')],
            'currency_id' => ['required', Rule::exists(Currency::class, 'id')],
            'language_id' => ['required', Rule::exists(Language::class, 'id')],
            'tax_zone_id' => ['nullable', Rule::exists(TaxZone::class, 'id')],
            // Tri-state: null inherits the global price display default.
            'prices_inc_tax' => ['nullable', 'boolean'],
            'default' => ['sometimes', 'boolean'],
            'countries' => ['sometimes', 'array'],
            'countries.*' => [Rule::exists(Country::class, 'id')],
        ];
    }

    /**
     * The validated input shaped for the region actions: optional keys
     * omitted when absent so an update leaves them untouched.
     *
     * @return array<string, mixed>
     */
    public function regionAttributes(): array
    {
        $validated = $this->validated();

        $attributes = [
            'name' => $validated['name'],
            'handle' => $validated['handle'],
            'channel_id' => $validated['channel_id'],
            'currency_id' => $validated['currency_id'],
            'language_id' => $validated['language_id'],
            'tax_zone_id' => $validated['tax_zone_id'] ?? null,
        ];

        if (array_key_exists('prices_inc_tax', $validated)) {
            $attributes['prices_inc_tax'] = $validated['prices_inc_tax'] === null ? null : (bool) $validated['prices_inc_tax'];
        }

        if (array_key_exists('default', $validated)) {
            $attributes['default'] = (bool) $validated['default'];
        }

        if (array_key_exists('countries', $validated)) {
            $attributes['countries'] = $validated['countries'];
        }

        return $attributes;
    }
}
