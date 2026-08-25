<?php

namespace Lunar\Panel\Support;

use Illuminate\Validation\Rule;
use Lunar\Core\Enums\SellingPolicy;
use Lunar\Core\Facades\Converter;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\TaxClass;

/**
 * The draftable field surface of a single variant — identifiers, tax,
 * shipping, ordering quantities, selling policy, enabled flag, and the
 * variant-morph attribute values. Shared by the variant edit page's draft
 * resource and the product page's simple shape, where the same keys ride the
 * product draft under a `variant:` prefix.
 */
class VariantFields
{
    /** @var array<int, string> */
    public const FIELDS = [
        'sku',
        'gtin',
        'mpn',
        'ean',
        'tax_class_id',
        'tax_ref',
        'shippable',
        'length_value',
        'length_unit',
        'width_value',
        'width_unit',
        'height_value',
        'height_unit',
        'weight_value',
        'weight_unit',
        'unit_quantity',
        'min_quantity',
        'quantity_increment',
        'backorder',
        'selling_policy',
        'enabled',
    ];

    public function __construct(protected AttributeSchema $attributeSchema) {}

    /**
     * Every draftable variant field key, including the variant-morph
     * attribute superset (which attributes actually apply depends on the
     * product's type, which rules() enforces).
     *
     * @return array<int, string>
     */
    public function fields(): array
    {
        return [
            ...self::FIELDS,
            ...$this->attributeSchema->fieldsForMorph(ProductVariant::morphName()),
        ];
    }

    /**
     * Current, normalised values per field key.
     *
     * @return array<string, mixed>
     */
    public function values(ProductVariant $variant): array
    {
        return $this->normalizeAll([
            'sku' => $variant->sku,
            'gtin' => $variant->gtin,
            'mpn' => $variant->mpn,
            'ean' => $variant->ean,
            'tax_class_id' => $variant->tax_class_id,
            'tax_ref' => $variant->tax_ref,
            'shippable' => $variant->shippable,
            'length_value' => $variant->length_value,
            'length_unit' => $variant->length_unit,
            'width_value' => $variant->width_value,
            'width_unit' => $variant->width_unit,
            'height_value' => $variant->height_value,
            'height_unit' => $variant->height_unit,
            'weight_value' => $variant->weight_value,
            'weight_unit' => $variant->weight_unit,
            'unit_quantity' => $variant->unit_quantity,
            'min_quantity' => $variant->min_quantity,
            'quantity_increment' => $variant->quantity_increment,
            'backorder' => $variant->backorder,
            'selling_policy' => $variant->selling_policy->value,
            'enabled' => $variant->enabled,
            ...$this->attributeSchema->values($variant),
        ]);
    }

    /**
     * Validation rules for a full variant value set.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(?ProductVariant $variant): array
    {
        $uniqueSku = Rule::unique((new ProductVariant)->getTable(), 'sku');

        if ($variant) {
            $uniqueSku->ignore($variant->getKey());
        }

        $measurements = Converter::getMeasurements();
        $lengthUnits = array_keys($measurements['length'] ?? []);
        $weightUnits = array_keys($measurements['weight'] ?? []);

        return [
            'sku' => ['nullable', 'string', 'max:255', $uniqueSku],
            'gtin' => ['nullable', 'string', 'max:255'],
            'mpn' => ['nullable', 'string', 'max:255'],
            'ean' => ['nullable', 'string', 'max:255'],
            'tax_class_id' => ['required', Rule::exists((new TaxClass)->getTable(), 'id')],
            'tax_ref' => ['nullable', 'string', 'max:255'],
            'shippable' => ['boolean'],
            'length_value' => ['nullable', 'numeric', 'min:0'],
            'length_unit' => ['nullable', Rule::in($lengthUnits)],
            'width_value' => ['nullable', 'numeric', 'min:0'],
            'width_unit' => ['nullable', Rule::in($lengthUnits)],
            'height_value' => ['nullable', 'numeric', 'min:0'],
            'height_unit' => ['nullable', Rule::in($lengthUnits)],
            'weight_value' => ['nullable', 'numeric', 'min:0'],
            'weight_unit' => ['nullable', Rule::in($weightUnits)],
            'unit_quantity' => ['integer', 'min:1'],
            'min_quantity' => ['integer', 'min:1'],
            'quantity_increment' => ['integer', 'min:1'],
            'backorder' => ['integer', 'min:0'],
            'selling_policy' => [Rule::in(array_column(SellingPolicy::cases(), 'value'))],
            'enabled' => ['boolean'],
            ...($variant ? $this->attributeSchema->rules($variant) : []),
        ];
    }

    /**
     * Conflict-dialog lang keys per field.
     *
     * @return array<string, string>
     */
    public function labels(): array
    {
        return [
            ...collect(self::FIELDS)
                ->mapWithKeys(fn (string $field) => [$field => "panel::products.variant_field_{$field}"])
                ->all(),
            ...$this->attributeSchema->labelsForMorph(ProductVariant::morphName()),
        ];
    }

    /**
     * Normalise a drafted value so equality against values() holds: booleans,
     * integers and measurements become real scalars rather than form strings.
     */
    public function normalizeValue(string $field, mixed $value): mixed
    {
        return match (true) {
            in_array($field, ['shippable', 'enabled'], true) => (bool) $value,
            in_array($field, ['unit_quantity', 'min_quantity', 'quantity_increment', 'backorder'], true) => (int) $value,
            in_array($field, ['tax_class_id'], true) => $value === null || $value === '' ? null : (int) $value,
            str_ends_with($field, '_value') => $value === null || $value === '' ? null : (float) $value,
            default => $value === '' ? null : $value,
        };
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    public function normalizeAll(array $values): array
    {
        foreach ($values as $field => $value) {
            if (! str_starts_with($field, AttributeSchema::PREFIX)) {
                $values[$field] = $this->normalizeValue($field, $value);
            }
        }

        return $values;
    }

    /**
     * Split a committed value set into the model attribute payload for
     * UpdatesProductVariant, overlaying drafted attribute values onto the
     * stored attribute data so untouched attributes survive the
     * whole-column write.
     *
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    public function commitPayload(ProductVariant $variant, array $values): array
    {
        $attributeValues = collect($values)
            ->filter(fn (mixed $value, string $key) => str_starts_with($key, AttributeSchema::PREFIX));

        $attributes = collect($values)->except($attributeValues->keys())->all();

        if ($attributeValues->isNotEmpty()) {
            $data = ($variant->attribute_data ?? collect())->all();

            foreach ($attributeValues as $key => $value) {
                $data[substr($key, strlen(AttributeSchema::PREFIX))] = $value;
            }

            $attributes['attribute_data'] = $data;
        }

        return $attributes;
    }
}
