<?php

namespace Lunar\Panel\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Lunar\Core\Facades\AttributeManifest;
use Lunar\Core\Facades\FieldTypeManifest;
use Lunar\Core\Facades\ModelManifest;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\AttributeGroup;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Rules\ValidRuleString;

/**
 * Shared by the attribute store and update endpoints. The type is only
 * accepted on store — like the Filament admin, an attribute's field type is
 * fixed once created.
 */
class AttributeRequest extends FormRequest
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
        /** @var Attribute|null $attribute */
        $attribute = $this->route('attribute');

        return [
            'name' => ['required', 'string', 'max:255'],
            'handle' => [
                'nullable', 'string', 'max:255',
                Rule::unique(Attribute::class, 'handle')->ignore($attribute?->id),
            ],
            'attribute_group_id' => ['nullable', Rule::exists(AttributeGroup::class, 'id')],
            'type' => [
                $attribute ? 'prohibited' : 'required',
                Rule::in(FieldTypeManifest::getTypes()->keys()->all()),
            ],
            'position' => ['sometimes', 'integer', 'min:1'],
            'required' => ['sometimes', 'boolean'],
            'validation_rules' => ['sometimes', 'nullable', 'array'],
            // Nullable: the empty-strings-to-null middleware turns blank
            // entries into nulls; they are dropped in attributeAttributes().
            'validation_rules.*' => ['nullable', 'string', 'max:255', new ValidRuleString],
            'searchable' => ['sometimes', 'boolean'],
            'filterable' => ['sometimes', 'boolean'],
            'model_types' => ['required', 'array', 'min:1'],
            'model_types.*' => [Rule::in($this->attributableMorphKeys())],
            'configuration' => ['sometimes', 'array'],
            ...$this->fieldTypeConfigurationRules($attribute),
        ];
    }

    /**
     * The validation rules the attribute's field type declares for its
     * configuration (FieldType::getConfig()), keyed under `configuration.`.
     * The type comes from the route attribute on update, the input on store.
     *
     * @return array<string, mixed>
     */
    protected function fieldTypeConfigurationRules(?Attribute $attribute): array
    {
        $type = $attribute?->type ?? $this->input('type');
        $class = is_string($type) ? FieldTypeManifest::getType($type) : null;

        if (! $class) {
            return [];
        }

        return collect((new $class)->getConfig()['options'] ?? [])
            ->mapWithKeys(fn (mixed $rules, string $key) => ['configuration.'.$key => $rules])
            ->all();
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $modelTypes = (array) $this->input('model_types', []);

            // Product attributes surface on variants already; carrying both would duplicate the field.
            if (in_array(Product::morphName(), $modelTypes, true) && in_array(ProductVariant::morphName(), $modelTypes, true)) {
                $validator->errors()->add('model_types', __('panel::attributes_settings.product_and_variant_invalid'));
            }
        });
    }

    /**
     * The validated input shaped for the attribute actions: flags cast, and
     * update-only keys omitted when absent so they stay untouched.
     *
     * @return array<string, mixed>
     */
    public function attributeAttributes(): array
    {
        $validated = $this->validated();

        $attributes = [
            'name' => $validated['name'],
            'handle' => $validated['handle'],
            'attribute_group_id' => $validated['attribute_group_id'] ?? null,
            'model_types' => $validated['model_types'],
        ];

        if (array_key_exists('type', $validated)) {
            $attributes['type'] = $validated['type'];
        }

        if (array_key_exists('position', $validated)) {
            $attributes['position'] = (int) $validated['position'];
        }

        foreach (['required', 'searchable', 'filterable'] as $flag) {
            if (array_key_exists($flag, $validated)) {
                $attributes[$flag] = (bool) $validated[$flag];
            }
        }

        if (array_key_exists('validation_rules', $validated)) {
            $rules = array_values(array_filter(
                $validated['validation_rules'] ?? [],
                fn (?string $rule): bool => $rule !== null && trim($rule) !== '',
            ));

            $attributes['validation_rules'] = $rules === [] ? null : $rules;
        }

        if (array_key_exists('configuration', $validated)) {
            $attributes['configuration'] = $validated['configuration'];
        }

        return $attributes;
    }

    /** @return array<int, string> */
    protected function attributableMorphKeys(): array
    {
        return AttributeManifest::getTypes()
            ->map(fn (string $type) => ModelManifest::getMorphMapKey($type))
            ->values()
            ->all();
    }
}
