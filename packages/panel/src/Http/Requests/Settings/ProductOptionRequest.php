<?php

namespace Lunar\Panel\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Lunar\Core\Enums\ProductOptionType;
use Lunar\Core\Models\ProductOption;

/**
 * Shared by the product option store and update endpoints. Name and label
 * arrive as locale-keyed maps from the translatable inputs and are merged over
 * any translations already stored. Values may carry a colour (colour options);
 * swatch images are handled separately through the media endpoints.
 */
class ProductOptionRequest extends FormRequest
{
    /** Handles are stored slugged, so normalise first and validate the stored form. */
    protected function prepareForValidation(): void
    {
        $handle = is_string($this->input('handle')) ? $this->input('handle') : '';

        $this->merge(['handle' => Str::slug($handle) ?: (Str::slug($this->defaultName()) ?: null)]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var ProductOption|null $productOption */
        $productOption = $this->route('productOption');

        return [
            'name' => ['required', 'array', 'min:1'],
            'name.*' => ['nullable', 'string', 'max:255'],
            'label' => ['sometimes', 'array'],
            'label.*' => ['nullable', 'string', 'max:255'],
            'handle' => [
                'nullable', 'string', 'max:255',
                Rule::unique(ProductOption::class, 'handle')->ignore($productOption?->id),
            ],
            'type' => ['sometimes', Rule::enum(ProductOptionType::class)],
            'shared' => ['sometimes', 'boolean'],
            'values' => ['sometimes', 'array'],
            'values.*.id' => ['nullable', 'integer'],
            'values.*.name' => ['required', 'array', 'min:1'],
            'values.*.name.*' => ['nullable', 'string', 'max:255'],
            'values.*.position' => ['sometimes', 'integer', 'min:1'],
            'values.*.colour' => ['nullable', 'string', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
        ];
    }

    /**
     * The validated input shaped for the product option actions: translation
     * maps merged over what is already stored, and optional keys omitted when
     * absent so they stay untouched.
     *
     * @return array<string, mixed>
     */
    public function productOptionAttributes(): array
    {
        $validated = $this->validated();

        /** @var ProductOption|null $productOption */
        $productOption = $this->route('productOption');

        $attributes = [
            'name' => $this->mergeTranslations($productOption?->name, $validated['name']),
            'handle' => $validated['handle'],
        ];

        if (array_key_exists('label', $validated)) {
            $attributes['label'] = $this->mergeTranslations($productOption?->label, $validated['label']);
        }

        if (array_key_exists('type', $validated)) {
            $attributes['type'] = $validated['type'];
        }

        if (array_key_exists('shared', $validated)) {
            $attributes['shared'] = (bool) $validated['shared'];
        }

        if (array_key_exists('values', $validated)) {
            $existingValues = $productOption
                ? $productOption->values()->get()->keyBy('id')
                : collect();

            $attributes['values'] = collect($validated['values'])->map(function (array $row, int $index) use ($existingValues): array {
                $existing = isset($row['id']) ? $existingValues->get((int) $row['id']) : null;

                return [
                    'id' => $row['id'] ?? null,
                    'name' => $this->mergeTranslations($existing?->name, $row['name']),
                    'position' => (int) ($row['position'] ?? $index + 1),
                    'colour' => $row['colour'] ?? null,
                ];
            })->all();
        }

        return $attributes;
    }

    /**
     * Merge a submitted locale map over the stored one, dropping blanks so the
     * map stays normalised.
     *
     * @param  mixed  $existing
     * @param  array<string, string|null>  $submitted
     * @return array<string, string>
     */
    protected function mergeTranslations($existing, array $submitted): array
    {
        $merged = array_merge((array) ($existing ?? []), $submitted);

        return array_filter($merged, fn ($value): bool => is_string($value) && $value !== '');
    }

    /** The default-locale name, used to derive a handle when none is supplied. */
    protected function defaultName(): string
    {
        $name = (array) $this->input('name', []);

        return (string) ($name[app()->getLocale()] ?? (collect($name)->first(fn ($value): bool => is_string($value) && $value !== '') ?? ''));
    }
}
