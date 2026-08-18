<?php

namespace Lunar\Panel\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Lunar\Core\Models\CustomerGroup;

/** Shared by the customer group store and update endpoints, whose rules are identical bar the handle unique scope. */
class CustomerGroupRequest extends FormRequest
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
        /** @var CustomerGroup|null $customerGroup */
        $customerGroup = $this->route('customerGroup');

        return [
            'name' => ['required', 'string', 'max:255'],
            'handle' => [
                'nullable', 'string', 'max:255',
                Rule::unique(CustomerGroup::class, 'handle')->ignore($customerGroup?->id),
            ],
            'default' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * The validated input shaped for the customer group actions: the default
     * flag omitted entirely when not supplied so an update leaves it untouched.
     *
     * @return array<string, mixed>
     */
    public function customerGroupAttributes(): array
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
