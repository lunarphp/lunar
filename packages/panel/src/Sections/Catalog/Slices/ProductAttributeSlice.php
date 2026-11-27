<?php

namespace Lunar\Panel\Sections\Catalog\Slices;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Contracts\Actions\Products\UpdatesProduct;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductType;
use Lunar\Panel\Drafts\Concerns\NormalizesDraftValues;
use Lunar\Panel\Forms\FormSlice;
use Lunar\Panel\Support\AttributeSchema;

/**
 * A product's mapped attribute values, one field per handle. Which handles
 * apply depends on the product type, which the same draft can change, so
 * fields() and currentValues() cover the morph-wide superset, rules() apply
 * each attribute only under the types that map it, and commit() writes only
 * the attributes of the type the product ends up with.
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

        // Every product attribute, not just the current type's: changing the
        // type on the edit page brings the new type's fields in without a
        // reload, so each needs its stored value seeded up front.
        $tokens = $this->stripPrefix($this->attributeSchema->tokensForMorph(Product::morphName()), AttributeSchema::PREFIX);

        return collect($this->stripPrefix($this->attributeSchema->valuesForMorph($record), AttributeSchema::PREFIX))
            ->map(fn (mixed $value, string $handle) => $this->normalizeAttributeValue($value, $tokens[$handle] ?? null))
            ->all();
    }

    public function normalize(array $data): array
    {
        return array_map(fn (mixed $value) => $this->normalizeAttributeValue($value), $data);
    }

    /**
     * The rules of every attribute mapped to a product type, each applying
     * only when the validated product_type_id is a type that maps it, so a
     * commit that changes the type validates against the new type's set.
     */
    public function rules(Model $record): array
    {
        /** @var Product $record */
        if (! $record->exists) {
            return [];
        }

        $attributes = [];
        $typeIds = [];

        foreach (ProductType::query()->with('productAttributes')->get() as $type) {
            foreach ($type->productAttributes as $attribute) {
                $attributes[$attribute->id] = $attribute;
                $typeIds[$attribute->id][] = $type->id;
            }
        }

        $rules = [];

        foreach ($attributes as $id => $attribute) {
            $only = 'exclude_unless:product_type_id,'.implode(',', $typeIds[$id]);

            foreach ($this->attributeSchema->rulesFor(new Collection([$attribute])) as $key => $fieldRules) {
                $rules[$key] = [$only, ...$fieldRules];
            }
        }

        return $this->stripPrefix($rules, AttributeSchema::PREFIX);
    }

    public function commit(Model $record, array $values): void
    {
        /** @var Product $record */
        // Only the attributes the product's type maps are written. The type
        // may have just changed in the same commit, so its mapping is read
        // afresh; values for the previous type's own attributes are dropped.
        $mapped = $this->stripPrefixFromList(
            $this->attributeSchema->fields($record->load('productType')),
            AttributeSchema::PREFIX,
        );

        $values = array_intersect_key($values, array_flip($mapped));

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
