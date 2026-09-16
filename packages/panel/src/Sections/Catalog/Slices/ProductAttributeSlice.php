<?php

namespace Lunar\Panel\Sections\Catalog\Slices;

use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Contracts\Actions\Products\UpdatesProduct;
use Lunar\Core\Models\Product;
use Lunar\Panel\Drafts\Concerns\NormalizesDraftValues;
use Lunar\Panel\Forms\FormSlice;
use Lunar\Panel\Support\AttributeSchema;

/**
 * A product's mapped attribute values, one field per handle. Which handles
 * apply depends on the product type, so fields() offers the morph-wide
 * superset and rules() narrows it to the record's mapping.
 */
class ProductAttributeSlice extends FormSlice
{
    use NormalizesDraftValues;

    public const KEY = 'attribute';

    public function __construct(
        protected UpdatesProduct $updatesProduct,
        protected AttributeSchema $attributeSchema,
    ) {}

    public function model(): string
    {
        return Product::class;
    }

    public function key(): string
    {
        return self::KEY;
    }

    public function fields(Model $record): array
    {
        return $this->stripPrefixFromList(
            $this->attributeSchema->fieldsForMorph(Product::morphName()),
            AttributeSchema::PREFIX,
        );
    }

    public function currentValues(Model $record): array
    {
        /** @var Product $record */
        // A product being created has no type yet, so no mapping to read.
        if (! $record->exists) {
            return [];
        }

        $tokens = $this->stripPrefix($this->attributeSchema->tokens($record), AttributeSchema::PREFIX);

        return collect($this->stripPrefix($this->attributeSchema->values($record), AttributeSchema::PREFIX))
            ->map(fn (mixed $value, string $handle) => $this->normalizeAttributeValue($value, $tokens[$handle] ?? null))
            ->all();
    }

    public function normalize(array $data): array
    {
        return array_map(fn (mixed $value) => $this->normalizeAttributeValue($value), $data);
    }

    public function rules(Model $record): array
    {
        /** @var Product $record */
        if (! $record->exists) {
            return [];
        }

        return $this->stripPrefix($this->attributeSchema->rules($record), AttributeSchema::PREFIX);
    }

    public function commit(Model $record, array $values): void
    {
        /** @var Product $record */
        if ($values === []) {
            return;
        }

        // Overlay the drafted values onto the stored set so attributes the
        // draft never touched survive the whole-column write.
        $data = ($record->attribute_data ?? collect())->all();

        foreach ($values as $handle => $value) {
            $data[$handle] = $value;
        }

        $this->updatesProduct->execute($record, ['attribute_data' => $data]);
    }

    public function labels(): array
    {
        return $this->stripPrefix($this->attributeSchema->labelsForMorph(Product::morphName()), AttributeSchema::PREFIX);
    }
}
