<?php

namespace Lunar\Panel\Sections\Catalog;

use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Contracts\Actions\Products\UpdatesProduct;
use Lunar\Core\Models\Product;
use Lunar\Panel\Drafts\DraftableResource;
use Lunar\Panel\Http\Requests\Products\ProductRequest;
use Lunar\Panel\Support\AttributeSchema;
use Lunar\Panel\Support\AvailabilitySchema;

class ProductDraftResource extends DraftableResource
{
    /** @var array<string, string>|null */
    protected ?array $attributeTokens = null;

    protected AvailabilitySchema $availabilitySchema;

    public function __construct(
        protected UpdatesProduct $updatesProduct,
        protected AttributeSchema $attributeSchema,
        AvailabilitySchema $availabilitySchema,
    ) {
        // Product customer-group rows carry the pivot's extra purchasable flag.
        $this->availabilitySchema = $availabilitySchema->withPurchasable();
    }

    public function model(): string
    {
        return Product::class;
    }

    public function fields(): array
    {
        return [
            'name',
            'status',
            'product_type_id',
            'brand_id',
            'short_description',
            'description',
            'tags',
            'collection_ids',
            // The morph-wide superset: which attributes actually apply
            // depends on the record's product type, which rules() enforces.
            ...$this->attributeSchema->fieldsForMorph(Product::morphName()),
            ...$this->availabilitySchema->fields(),
        ];
    }

    public function currentValues(Model $record): array
    {
        /** @var Product $record */
        return [
            'name' => $this->translationMap($record->name?->all() ?? []),
            'status' => $record->status->getValue(),
            'product_type_id' => $record->product_type_id,
            'brand_id' => $record->brand_id,
            'short_description' => $this->translationMap($record->short_description?->all() ?? []),
            'description' => $this->translationMap($record->description?->all() ?? []),
            'tags' => $this->sortedTags($record->tags()->pluck('value')->all()),
            'collection_ids' => $this->sortedIds($record->collections()->allRelatedIds()->all()),
            ...collect($this->attributeSchema->values($record))
                ->map(fn (mixed $value, string $key) => $this->normalizeAttributeValue($value, $this->attributeTokens($record)[$key] ?? null))
                ->all(),
            ...$this->availabilitySchema->values($record),
        ];
    }

    public function normalize(array $data): array
    {
        foreach (['name', 'short_description', 'description'] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = $this->translationMap((array) $data[$field]);
            }
        }

        if (array_key_exists('tags', $data)) {
            $data['tags'] = $this->sortedTags((array) $data['tags']);
        }

        if (array_key_exists('collection_ids', $data)) {
            $data['collection_ids'] = $this->sortedIds((array) $data['collection_ids']);
        }

        foreach ($data as $key => $value) {
            if (str_starts_with($key, AttributeSchema::PREFIX)) {
                $data[$key] = $this->normalizeAttributeValue($value);
            }

            if (str_starts_with($key, AvailabilitySchema::CHANNEL_PREFIX)
                || str_starts_with($key, AvailabilitySchema::CUSTOMER_GROUP_PREFIX)) {
                $data[$key] = $this->availabilitySchema->normalizeValue((array) $value);
            }
        }

        return $data;
    }

    public function rules(Model $record): array
    {
        /** @var Product $record */
        return [
            ...ProductRequest::rulesFor($record),
            ...$this->attributeSchema->rules($record),
            ...$this->availabilitySchema->rules(),
        ];
    }

    public function commit(Model $record, array $values): void
    {
        /** @var Product $record */
        // Drafted availability rows split off and rebuild the full pivot maps
        // (untouched rows ride along — the sync replaces the whole set).
        $availability = $this->availabilitySchema->extract($record, $values);

        $values = $availability['attributes'];

        $tags = array_key_exists('tags', $values)
            ? array_map('strval', (array) $values['tags'])
            : null;

        $collectionIds = array_key_exists('collection_ids', $values)
            ? array_map('intval', (array) $values['collection_ids'])
            : null;

        $attributeValues = collect($values)
            ->filter(fn (mixed $value, string $key) => str_starts_with($key, AttributeSchema::PREFIX));

        $attributes = collect($values)
            ->except([...$attributeValues->keys(), 'tags', 'collection_ids'])
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

        $this->updatesProduct->execute(
            $record,
            $attributes,
            $tags,
            $collectionIds,
            $availability['channels'],
            $availability['customerGroups'],
        );
    }

    public function labels(): array
    {
        return [
            'name' => 'panel::products.field_name',
            'status' => 'panel::products.field_status',
            'product_type_id' => 'panel::products.field_product_type',
            'brand_id' => 'panel::products.field_brand',
            'short_description' => 'panel::products.field_short_description',
            'description' => 'panel::products.field_description',
            'tags' => 'panel::products.field_tags',
            'collection_ids' => 'panel::products.side_collections',
            ...$this->availabilitySchema->labels(),
        ];
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
     * Tags compare as an uppercased sorted set — the Tag model uppercases
     * values on write, so drafted input must normalise the same way for
     * equality against the stored set to hold.
     *
     * @param  array<int, mixed>  $tags
     * @return array<int, string>
     */
    protected function sortedTags(array $tags): array
    {
        $tags = array_values(array_unique(array_filter(array_map(
            fn (mixed $tag) => mb_strtoupper(trim((string) $tag)),
            $tags,
        ), fn (string $tag) => $tag !== '')));

        sort($tags);

        return $tags;
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
    protected function attributeTokens(Product $record): array
    {
        return $this->attributeTokens ??= $this->attributeSchema->tokens($record);
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
