<?php

namespace Lunar\Panel\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Lunar\Core\Models\Location;

/** Shared by the location store and update endpoints, whose rules are identical bar the handle unique scope. */
class LocationRequest extends FormRequest
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
        /** @var Location|null $location */
        $location = $this->route('location');

        return [
            'name' => ['required', 'string', 'max:255'],
            'handle' => [
                'nullable', 'string', 'max:255',
                Rule::unique(Location::class, 'handle')->ignore($location?->id),
            ],
            'default' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * The validated input shaped for the location actions: the default flag
     * omitted when absent so an update leaves it untouched.
     *
     * @return array<string, mixed>
     */
    public function locationAttributes(): array
    {
        $validated = $this->validated();

        $attributes = [
            'name' => $validated['name'],
            'handle' => $validated['handle'],
        ];

        if (array_key_exists('default', $validated)) {
            $attributes['default'] = (bool) $validated['default'];
        }

        return $attributes;
    }
}
