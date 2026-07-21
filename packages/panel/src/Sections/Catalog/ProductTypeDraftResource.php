<?php

namespace Lunar\Panel\Sections\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Lunar\Core\Contracts\Actions\ProductTypes\UpdatesProductType;
use Lunar\Core\Models\ProductType;
use Lunar\Panel\Drafts\DraftableResource;
use Lunar\Panel\Http\Requests\ProductTypes\ProductTypeRequest;
use Lunar\Panel\Support\AttributeSchema;

class ProductTypeDraftResource extends DraftableResource
{
    /** @var array<string, string>|null */
    protected ?array $attributeTokens = null;

    public function __construct(
        protected UpdatesProductType $updatesProductType,
        protected AttributeSchema $attributeSchema,
    ) {}

    public function model(): string
    {
        return ProductType::class;
    }

    public function fields(): array
    {
        return [
            'name',
            'handle',
            'status',
            'description',
            'default_tax_class_id',
            'product_attribute_ids',
            'variant_attribute_ids',
            ...$this->attributeSchema->fields(new ProductType),
        ];
    }

    public function currentValues(Model $record): array
    {
        /** @var ProductType $record */
        return [
            'name' => $record->name,
            'handle' => $record->handle,
            'status' => $record->status->getValue(),
            'description' => $record->description,
            'default_tax_class_id' => $record->default_tax_class_id,
            'product_attribute_ids' => $this->sortedIds($this->surfaceIds($record->productAttributes())),
            'variant_attribute_ids' => $this->sortedIds($this->surfaceIds($record->variantAttributes())),
            ...collect($this->attributeSchema->values($record))
                ->map(fn (mixed $value, string $key) => $this->normalizeAttributeValue($value, $this->attributeTokens()[$key] ?? null))
                ->all(),
        ];
    }

    public function normalize(array $data): array
    {
        foreach (['product_attribute_ids', 'variant_attribute_ids'] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = $this->sortedIds((array) $data[$field]);
            }
        }

        foreach ($data as $key => $value) {
            if (str_starts_with($key, AttributeSchema::PREFIX)) {
                $data[$key] = $this->normalizeAttributeValue($value, $this->attributeTokens()[$key] ?? null);
            }
        }

        return $data;
    }

    public function rules(Model $record): array
    {
        /** @var ProductType $record */
        return [
            ...ProductTypeRequest::rulesFor($record),
            ...$this->attributeSchema->rules($record),
        ];
    }

    public function commit(Model $record, array $values): void
    {
        /** @var ProductType $record */
        $attributeValues = collect($values)
            ->filter(fn (mixed $value, string $key) => str_starts_with($key, AttributeSchema::PREFIX));

        $attributeIds = null;

        if (array_key_exists('product_attribute_ids', $values) || array_key_exists('variant_attribute_ids', $values)) {
            // A draft may touch one surface only; the other keeps the type's
            // current mapping so a product-attributes edit never clears the
            // variant mapping (and vice versa).
            $productIds = array_key_exists('product_attribute_ids', $values)
                ? array_map('intval', (array) $values['product_attribute_ids'])
                : $this->surfaceIds($record->productAttributes());

            $variantIds = array_key_exists('variant_attribute_ids', $values)
                ? array_map('intval', (array) $values['variant_attribute_ids'])
                : $this->surfaceIds($record->variantAttributes());

            $attributeIds = array_values(array_unique([...$productIds, ...$variantIds]));
        }

        $attributes = collect($values)
            ->except([...$attributeValues->keys(), 'product_attribute_ids', 'variant_attribute_ids'])
            ->all();

        if ($attributeValues->isNotEmpty()) {
            // Overlay the drafted values onto the stored set so attributes the
            // draft never touched survive the whole-column write.
            $data = ($record->attribute_data ?? collect())->all();

            foreach ($attributeValues as $key => $value) {
                $data[substr($key, strlen(AttributeSchema::PREFIX))] = $value;
            }

            $attributes['attribute_data'] = $data;
        }

        $this->updatesProductType->execute($record, $attributes, $attributeIds);
    }

    public function labels(): array
    {
        return [
            'name' => 'panel::product-types.field_name',
            'handle' => 'panel::product-types.field_handle',
            'status' => 'panel::product-types.field_status',
            'description' => 'panel::product-types.field_description',
            'default_tax_class_id' => 'panel::product-types.field_default_tax_class',
            'product_attribute_ids' => 'panel::product-types.section_product_attributes',
            'variant_attribute_ids' => 'panel::product-types.section_variant_attributes',
            ...$this->attributeSchema->labels(new ProductType),
        ];
    }

    /**
     * Attribute ids for one mapping surface, plucked through the filtered
     * relation — allRelatedIds() queries the pivot directly and would skip
     * the morph filter. The key is qualified because the pivot join makes a
     * bare `id` ambiguous.
     *
     * @return array<int, int>
     */
    protected function surfaceIds(BelongsToMany $relation): array
    {
        return $relation->pluck($relation->getRelated()->getQualifiedKeyName())->all();
    }

    /**
     * @param  array<int, mixed>  $ids
     * @return array<int, int>
     */
    protected function sortedIds(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));

        sort($ids);

        return $ids;
    }

    /**
     * Attribute values arrive in whatever shape their field type stores;
     * translated-text maps get key-sorted with blank entries dropped so
     * equality against the stored value holds. Sequential arrays keep their
     * order, as do keyed list values.
     */
    protected function normalizeAttributeValue(mixed $value, ?string $token = null): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (array_is_list($value)) {
            return $value;
        }

        if ($token === 'list') {
            return array_map(fn (mixed $item) => is_string($item) ? $item : (string) $item, $value);
        }

        return $this->translationMap($value);
    }

    /**
     * Field-type tokens per draft field key, memoized per request.
     *
     * @return array<string, string>
     */
    protected function attributeTokens(): array
    {
        return $this->attributeTokens ??= $this->attributeSchema->tokens(new ProductType);
    }

    /**
     * Normalise a `{locale: text}` translation map so equality against the
     * stored value holds: empty values are dropped and keys are sorted.
     *
     * @param  array<string, mixed>  $map
     * @return array<string, string>
     */
    protected function translationMap(array $map): array
    {
        $map = array_filter(
            array_map(fn (mixed $value) => is_string($value) ? $value : (string) $value, $map),
            fn (string $value) => $value !== '',
        );

        ksort($map);

        return $map;
    }
}
