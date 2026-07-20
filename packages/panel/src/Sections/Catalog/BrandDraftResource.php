<?php

namespace Lunar\Panel\Sections\Catalog;

use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Contracts\Actions\Brands\UpdatesBrand;
use Lunar\Core\Models\Brand;
use Lunar\Panel\Drafts\DraftableResource;
use Lunar\Panel\Http\Requests\Brands\BrandRequest;
use Lunar\Panel\Support\AttributeSchema;

class BrandDraftResource extends DraftableResource
{
    /** @var array<string, string>|null */
    protected ?array $attributeTokens = null;

    public function __construct(
        protected UpdatesBrand $updatesBrand,
        protected AttributeSchema $attributeSchema,
    ) {}

    public function model(): string
    {
        return Brand::class;
    }

    public function fields(): array
    {
        return [
            'name',
            'handle',
            'status',
            'short_description',
            'description',
            'collection_ids',
            ...$this->attributeSchema->fields(new Brand),
        ];
    }

    public function currentValues(Model $record): array
    {
        /** @var Brand $record */
        return [
            'name' => $record->name,
            'handle' => $record->handle,
            'status' => $record->status->getValue(),
            'short_description' => $this->translationMap($record->short_description?->all() ?? []),
            'description' => $this->translationMap($record->description?->all() ?? []),
            'collection_ids' => $this->sortedIds($record->collections()->allRelatedIds()->all()),
            ...collect($this->attributeSchema->values($record))
                ->map(fn (mixed $value, string $key) => $this->normalizeAttributeValue($value, $this->attributeTokens()[$key] ?? null))
                ->all(),
        ];
    }

    public function normalize(array $data): array
    {
        foreach (['short_description', 'description'] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = $this->translationMap((array) $data[$field]);
            }
        }

        if (array_key_exists('collection_ids', $data)) {
            $data['collection_ids'] = $this->sortedIds((array) $data['collection_ids']);
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
        /** @var Brand $record */
        return [
            ...BrandRequest::rulesFor($record),
            ...$this->attributeSchema->rules($record),
        ];
    }

    public function commit(Model $record, array $values): void
    {
        /** @var Brand $record */
        $attributeValues = collect($values)
            ->filter(fn (mixed $value, string $key) => str_starts_with($key, AttributeSchema::PREFIX));

        $collectionIds = array_key_exists('collection_ids', $values)
            ? array_map('intval', (array) $values['collection_ids'])
            : null;

        $attributes = collect($values)
            ->except([...$attributeValues->keys(), 'collection_ids'])
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

        $this->updatesBrand->execute($record, $attributes, $collectionIds);
    }

    public function labels(): array
    {
        return [
            'name' => 'panel::brands.field_name',
            'handle' => 'panel::brands.field_handle',
            'status' => 'panel::brands.field_status',
            'short_description' => 'panel::brands.field_short_description',
            'description' => 'panel::brands.field_description',
            'collection_ids' => 'panel::brands.side_collections',
            ...$this->attributeSchema->labels(new Brand),
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
     * Attribute values arrive in whatever shape their field type stores;
     * translated-text maps get key-sorted with blank entries dropped so
     * equality against the stored value holds. Sequential arrays keep their
     * order, as do keyed list values (the Filament admin's KeyValue editor
     * writes them and lets staff reorder entries by hand).
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
        return $this->attributeTokens ??= $this->attributeSchema->tokens(new Brand);
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
