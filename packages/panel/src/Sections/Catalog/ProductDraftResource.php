<?php

namespace Lunar\Panel\Sections\Catalog;

use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Contracts\Actions\Products\UpdatesProduct;
use Lunar\Core\Models\Product;
use Lunar\Panel\Drafts\Concerns\NormalizesDraftValues;
use Lunar\Panel\Drafts\DraftableResource;
use Lunar\Panel\Http\Requests\Products\ProductRequest;

/**
 * The product's own columns and relations. Attribute values, availability
 * rows and the simple-shape sole variant ride the same draft as form slices
 * (see Slices/), composed onto this resource by the panel.
 */
class ProductDraftResource extends DraftableResource
{
    use NormalizesDraftValues;

    public function __construct(protected UpdatesProduct $updatesProduct) {}

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

        return $data;
    }

    public function rules(Model $record): array
    {
        /** @var Product $record */
        return ProductRequest::rulesFor($record);
    }

    public function commit(Model $record, array $values): void
    {
        /** @var Product $record */
        $tags = array_key_exists('tags', $values)
            ? array_map('strval', (array) $values['tags'])
            : null;

        $collectionIds = array_key_exists('collection_ids', $values)
            ? array_map('intval', (array) $values['collection_ids'])
            : null;

        $this->updatesProduct->execute(
            $record,
            collect($values)->except(['tags', 'collection_ids'])->all(),
            $tags,
            $collectionIds,
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
        ];
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
}
