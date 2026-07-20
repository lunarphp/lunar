<?php

namespace Lunar\Panel\Sections\Catalog;

use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Contracts\Actions\Collections\UpdatesCollection;
use Lunar\Core\Models\Collection;
use Lunar\Panel\Drafts\DraftableResource;
use Lunar\Panel\Http\Requests\Collections\CollectionRequest;
use Lunar\Panel\Support\AttributeSchema;
use Lunar\Panel\Support\AvailabilitySchema;

class CollectionDraftResource extends DraftableResource
{
    /** @var array<string, string>|null */
    protected ?array $attributeTokens = null;

    public function __construct(
        protected UpdatesCollection $updatesCollection,
        protected AttributeSchema $attributeSchema,
        protected AvailabilitySchema $availabilitySchema,
    ) {}

    public function model(): string
    {
        return Collection::class;
    }

    public function fields(): array
    {
        return [
            'name',
            'handle',
            'status',
            'sort',
            'short_description',
            'description',
            ...$this->attributeSchema->fields(new Collection),
            ...$this->availabilitySchema->fields(),
        ];
    }

    public function currentValues(Model $record): array
    {
        /** @var Collection $record */
        return [
            'name' => $this->translationMap($record->name?->all() ?? []),
            'handle' => $record->handle,
            'status' => $record->status->getValue(),
            'sort' => $record->sort,
            'short_description' => $this->translationMap($record->short_description?->all() ?? []),
            'description' => $this->translationMap($record->description?->all() ?? []),
            ...collect($this->attributeSchema->values($record))
                ->map(fn (mixed $value, string $key) => $this->normalizeAttributeValue($value, $this->attributeTokens()[$key] ?? null))
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

        foreach ($data as $key => $value) {
            if (str_starts_with($key, AttributeSchema::PREFIX)) {
                $data[$key] = $this->normalizeAttributeValue($value, $this->attributeTokens()[$key] ?? null);
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
        /** @var Collection $record */
        return [
            ...CollectionRequest::rulesFor($record),
            ...$this->attributeSchema->rules($record),
            ...$this->availabilitySchema->rules(),
        ];
    }

    public function commit(Model $record, array $values): void
    {
        /** @var Collection $record */
        // Drafted availability rows split off and rebuild the full pivot maps
        // (untouched rows ride along — the sync replaces the whole set).
        $availability = $this->availabilitySchema->extract($record, $values);

        $attributeValues = collect($availability['attributes'])
            ->filter(fn (mixed $value, string $key) => str_starts_with($key, AttributeSchema::PREFIX));

        $attributes = collect($availability['attributes'])
            ->except($attributeValues->keys())
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

        $this->updatesCollection->execute(
            $record,
            $attributes,
            $availability['channels'],
            $availability['customerGroups'],
        );
    }

    public function labels(): array
    {
        return [
            'name' => 'panel::collections.field_name',
            'handle' => 'panel::collections.field_handle',
            'status' => 'panel::collections.field_status',
            'sort' => 'panel::collections.field_sort',
            'short_description' => 'panel::collections.field_short_description',
            'description' => 'panel::collections.field_description',
            ...$this->attributeSchema->labels(new Collection),
            ...$this->availabilitySchema->labels(),
        ];
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
        return $this->attributeTokens ??= $this->attributeSchema->tokens(new Collection);
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
