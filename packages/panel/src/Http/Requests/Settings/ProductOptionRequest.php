<?php

namespace Lunar\Panel\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Lunar\Core\Models\ProductOption;

/**
 * Shared by the product option store and update endpoints. Names arrive as
 * plain strings and are written to the app locale, preserving any other
 * translations already stored.
 */
class ProductOptionRequest extends FormRequest
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
        /** @var ProductOption|null $productOption */
        $productOption = $this->route('productOption');

        return [
            'name' => ['required', 'string', 'max:255'],
            'handle' => [
                'nullable', 'string', 'max:255',
                Rule::unique(ProductOption::class, 'handle')->ignore($productOption?->id),
            ],
            'shared' => ['sometimes', 'boolean'],
            'values' => ['sometimes', 'array'],
            'values.*.id' => ['nullable', 'integer'],
            'values.*.name' => ['required', 'string', 'max:255'],
            'values.*.position' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    /**
     * The validated input shaped for the product option actions: names merged
     * into the app locale, and optional keys omitted when absent so they
     * stay untouched.
     *
     * @return array<string, mixed>
     */
    public function productOptionAttributes(): array
    {
        $validated = $this->validated();
        $locale = app()->getLocale();

        /** @var ProductOption|null $productOption */
        $productOption = $this->route('productOption');

        $existingName = $productOption ? (array) ($productOption->name ?? []) : [];

        $attributes = [
            'name' => array_merge($existingName, [$locale => $validated['name']]),
            'handle' => $validated['handle'],
        ];

        if (array_key_exists('shared', $validated)) {
            $attributes['shared'] = (bool) $validated['shared'];
        }

        if (array_key_exists('values', $validated)) {
            $existingValues = $productOption
                ? $productOption->values()->get()->keyBy('id')
                : collect();

            $attributes['values'] = collect($validated['values'])->map(function (array $row, int $index) use ($existingValues, $locale): array {
                $existing = isset($row['id']) ? $existingValues->get((int) $row['id']) : null;

                return [
                    'id' => $row['id'] ?? null,
                    'name' => array_merge($existing ? (array) ($existing->name ?? []) : [], [$locale => $row['name']]),
                    'position' => (int) ($row['position'] ?? $index + 1),
                ];
            })->all();
        }

        return $attributes;
    }
}
