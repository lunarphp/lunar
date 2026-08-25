<?php

namespace Lunar\Panel\Support;

use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Lunar\Core\Enums\FieldTypeEnum;
use Lunar\Core\Models\Attribute;

/**
 * Serializes a model's mapped attributes into the shape the panel's
 * AttributeFields component renders, and derives the validation rules and
 * draft plumbing for the same set. Values travel keyed `attribute:{handle}`
 * (a colon, not a dot, so Laravel's validator treats each key as flat rather
 * than a nested path).
 */
class AttributeSchema
{
    public const PREFIX = 'attribute:';

    /**
     * The mapped attributes grouped for rendering: group header plus the
     * fields it carries, both in position order.
     *
     * @return array<int, array{handle: string, name: string, fields: array<int, array<string, mixed>>}>
     */
    public function groups(Model $model): array
    {
        return $this->grouped(
            $this->attributes($model),
            'fields',
            fn (Attribute $attribute) => $this->field($attribute),
        );
    }

    /**
     * Every attribute assignable to the given morph type, grouped for an
     * attribute picker (the product type mapping UI): group header plus
     * attribute rows, both in position order.
     *
     * @return array<int, array{handle: string, name: string, attributes: array<int, array<string, mixed>>}>
     */
    public function pickerGroups(string $morphType): array
    {
        $attributes = Attribute::query()
            ->whereHas('models', fn ($query) => $query->where('model_type', $morphType))
            ->with('group')
            ->orderBy('position')
            ->get();

        return $this->grouped($attributes, 'attributes', fn (Attribute $attribute) => [
            'id' => $attribute->id,
            'name' => $attribute->name,
            'handle' => $attribute->handle,
            'type' => $attribute->type,
            'required' => (bool) $attribute->required,
        ]);
    }

    /**
     * Group attribute rows by their AttributeGroup: group header plus the
     * mapped items under $itemsKey, both in position order.
     *
     * @param  Collection<int, Attribute>  $attributes
     * @param  Closure(Attribute): array<string, mixed>  $mapItem
     * @return array<int, array<string, mixed>>
     */
    protected function grouped(Collection $attributes, string $itemsKey, Closure $mapItem): array
    {
        return $attributes
            ->groupBy(fn (Attribute $attribute) => $attribute->attribute_group_id ?? 0)
            ->map(function ($attributes) use ($itemsKey, $mapItem) {
                $group = $attributes->first()->group;

                return [
                    'handle' => $group?->handle ?? 'other',
                    'name' => $group?->name ?? __('panel::attributes.ungrouped'),
                    'position' => $group?->position ?? PHP_INT_MAX,
                    $itemsKey => $attributes->map($mapItem)->values()->all(),
                ];
            })
            ->sortBy('position')
            ->map(fn (array $group) => collect($group)->except('position')->all())
            ->values()
            ->all();
    }

    /**
     * Current values for every mapped attribute, keyed `attribute:{handle}`.
     * Attributes without a stored value get null so the draft layer has a
     * stable field set.
     *
     * @return array<string, mixed>
     */
    public function values(Model $model): array
    {
        $stored = $model->attribute_data;

        return $this->attributes($model)
            ->mapWithKeys(fn (Attribute $attribute) => [
                static::PREFIX.$attribute->handle => $this->raw($stored?->get($attribute->handle)?->jsonSerialize()),
            ])
            ->all();
    }

    /**
     * Flatten a field type's serialisation to plain scalars/arrays — some
     * (TranslatedText) serialise to collections of nested field types, which
     * would fail `array` validation rules and confuse draft comparisons.
     */
    protected function raw(mixed $value): mixed
    {
        return is_object($value) || is_array($value)
            ? json_decode((string) json_encode($value), true)
            : $value;
    }

    /**
     * Draftable field keys for the model's mapped attributes.
     *
     * @return array<int, string>
     */
    public function fields(Model $model): array
    {
        return $this->attributes($model)
            ->map(fn (Attribute $attribute) => static::PREFIX.$attribute->handle)
            ->values()
            ->all();
    }

    /**
     * Every draft field key assignable to a morph type, regardless of any
     * per-record mapping — the allow-list shape for resources (products)
     * whose applicable attribute set depends on the record.
     *
     * @return array<int, string>
     */
    public function fieldsForMorph(string $morphType): array
    {
        return Attribute::query()
            ->whereHas('models', fn ($query) => $query->where('model_type', $morphType))
            ->orderBy('position')
            ->get()
            ->map(fn (Attribute $attribute) => static::PREFIX.$attribute->handle)
            ->values()
            ->all();
    }

    /**
     * Human labels keyed by draft field key, for every attribute attached to
     * a morph type. Unlike labels(), this needs no hydrated model, so it
     * suits models whose attribute mapping hangs off a relation (a product's
     * product type) that a fresh instance does not have.
     *
     * @return array<string, string>
     */
    public function labelsForMorph(string $morphType): array
    {
        return Attribute::query()
            ->whereHas('models', fn ($query) => $query->where('model_type', $morphType))
            ->orderBy('position')
            ->get()
            ->mapWithKeys(fn (Attribute $attribute) => [static::PREFIX.$attribute->handle => $attribute->name])
            ->all();
    }

    /**
     * Human labels keyed by draft field key.
     *
     * @return array<string, string>
     */
    public function labels(Model $model): array
    {
        return $this->attributes($model)
            ->mapWithKeys(fn (Attribute $attribute) => [static::PREFIX.$attribute->handle => $attribute->name])
            ->all();
    }

    /**
     * Validation rules per attribute, keyed `attribute:{handle}`.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(Model $model): array
    {
        $rules = [];

        foreach ($this->attributes($model) as $attribute) {
            $token = $this->token($attribute);
            $key = static::PREFIX.$attribute->handle;

            $base = [$attribute->required ? 'required' : 'nullable'];

            $rules[$key] = [...$base, ...match ($token) {
                'number' => ['numeric'],
                'toggle' => ['boolean'],
                'dropdown' => ['string', Rule::in($this->dropdownValues($attribute))],
                'translated_text', 'list' => ['array'],
                'unknown', 'file' => [],
                default => ['string'],
            }];

            if (in_array($token, ['translated_text', 'list'], true)) {
                $rules[$key.'.*'] = ['nullable', 'string'];
            }

            if ($token === 'list' && ($maxItems = (int) ($attribute->configuration?->get('max_items') ?? 0)) > 0) {
                $rules[$key][] = 'max:'.$maxItems;
            }

            // Staff-authored rules (spec 0062) describe a single stored value,
            // so multi-value types apply them per entry. Unknown types render
            // read-only and never submit a value to validate.
            $custom = array_values(array_filter(
                $attribute->validation_rules ?? [],
                fn (mixed $rule): bool => is_string($rule) && $rule !== '',
            ));

            if ($custom !== [] && $token !== 'unknown') {
                $target = in_array($token, ['translated_text', 'list'], true) ? $key.'.*' : $key;

                $rules[$target] = [...$rules[$target], ...$custom];
            }
        }

        return $rules;
    }

    /**
     * Field-type tokens keyed by draft field key, for consumers that need
     * type-aware value handling.
     *
     * @return array<string, string>
     */
    public function tokens(Model $model): array
    {
        return $this->attributes($model)
            ->mapWithKeys(fn (Attribute $attribute) => [static::PREFIX.$attribute->handle => $this->token($attribute)])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function field(Attribute $attribute): array
    {
        $token = $this->token($attribute);

        $config = match ($token) {
            'text' => ['richtext' => (bool) $attribute->configuration?->get('richtext')],
            'dropdown' => ['options' => $this->dropdownOptions($attribute)],
            'number' => [
                'min' => $attribute->configuration?->get('min'),
                'max' => $attribute->configuration?->get('max'),
            ],
            'list' => ['max_items' => $attribute->configuration?->get('max_items')],
            default => [],
        };

        return [
            'key' => static::PREFIX.$attribute->handle,
            'handle' => $attribute->handle,
            'label' => $attribute->name,
            'required' => $attribute->required,
            'type' => $token,
            'config' => (object) $config,
        ];
    }

    /**
     * The token the frontend switches on: the attribute's type string when it
     * is one of the built-in FieldTypeEnum values, `unknown` otherwise
     * (consumer-registered types render read-only until they ship an editor).
     */
    protected function token(Attribute $attribute): string
    {
        return FieldTypeEnum::tryFrom($attribute->type)?->value ?? 'unknown';
    }

    /**
     * Lookups are stored as rows of `{label, value}` (the shape core's
     * Dropdown field type declares); earlier panel builds saved a
     * `label => value` map, so both shapes normalise here.
     *
     * @return array<int, array{label: string, value: string}>
     */
    protected function dropdownOptions(Attribute $attribute): array
    {
        return collect($attribute->configuration?->get('lookups') ?? [])
            ->map(function (mixed $lookup, int|string $key): ?array {
                if (! is_array($lookup)) {
                    $label = is_string($key) ? $key : (string) $lookup;
                    $value = is_scalar($lookup) ? (string) $lookup : '';

                    return $label === '' ? null : ['label' => $label, 'value' => $value !== '' ? $value : $label];
                }

                $label = $lookup['label'] ?? null;

                if (! is_string($label) || $label === '') {
                    return null;
                }

                return ['label' => $label, 'value' => (string) ($lookup['value'] ?? $label)];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    protected function dropdownValues(Attribute $attribute): array
    {
        return collect($this->dropdownOptions($attribute))->pluck('value')->all();
    }

    /**
     * @return Collection<int, Attribute>
     */
    protected function attributes(Model $model): Collection
    {
        // mappedAttributes() returns a loaded, position-ordered collection.
        // A product's mapping carries both morphs (its type's product AND
        // variant attributes), so the set is filtered down to the attributes
        // applicable to this model's own morph type.
        $morph = $model->getMorphClass();

        return $model->mappedAttributes()
            ->load(['group', 'models'])
            ->filter(fn (Attribute $attribute) => $attribute->models->contains('model_type', $morph))
            ->values();
    }
}
