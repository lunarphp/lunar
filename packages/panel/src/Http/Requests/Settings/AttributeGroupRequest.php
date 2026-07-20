<?php

namespace Lunar\Panel\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Lunar\Core\Models\AttributeGroup;

/** Shared by the attribute group store and update endpoints, whose rules are identical bar the handle unique scope. */
class AttributeGroupRequest extends FormRequest
{
    /** Handles are stored snake-slugged, so normalise first and validate the stored form. */
    protected function prepareForValidation(): void
    {
        $handle = is_string($this->input('handle')) ? $this->input('handle') : '';
        $name = is_string($this->input('name')) ? $this->input('name') : '';

        $this->merge(['handle' => Str::slug($handle, '_') ?: (Str::slug($name, '_') ?: null)]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var AttributeGroup|null $attributeGroup */
        $attributeGroup = $this->route('attributeGroup');

        return [
            'name' => ['required', 'string', 'max:255'],
            'handle' => [
                'nullable', 'string', 'max:255',
                Rule::unique(AttributeGroup::class, 'handle')->ignore($attributeGroup?->id),
            ],
            'position' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    /**
     * The validated input shaped for the attribute group actions.
     *
     * @return array<string, mixed>
     */
    public function attributeGroupAttributes(): array
    {
        $validated = $this->validated();

        $attributes = [
            'name' => $validated['name'],
            'handle' => $validated['handle'],
        ];

        if (array_key_exists('position', $validated)) {
            $attributes['position'] = (int) $validated['position'];
        }

        return $attributes;
    }
}
