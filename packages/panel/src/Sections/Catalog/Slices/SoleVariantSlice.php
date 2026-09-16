<?php

namespace Lunar\Panel\Sections\Catalog\Slices;

use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Contracts\Actions\Products\UpdatesProductVariant;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Panel\Drafts\Concerns\NormalizesDraftValues;
use Lunar\Panel\Drafts\DraftSlice;
use Lunar\Panel\Support\AttributeSchema;
use Lunar\Panel\Support\VariantFields;

/**
 * Simple-shape products (one variant, no options) edit their sole variant
 * inline, so its fields ride the product draft under `variant:` and keep a
 * single save cluster. Multi-variant products edit variants on their own
 * pages; here every variant field is refused outright.
 */
class SoleVariantSlice extends DraftSlice
{
    use NormalizesDraftValues;

    public const KEY = 'variant';

    public function __construct(
        protected UpdatesProductVariant $updatesProductVariant,
        protected VariantFields $variantFields,
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
        return $this->variantFields->fields();
    }

    public function currentValues(Model $record): array
    {
        /** @var Product $record */
        $variant = $this->soleVariant($record);

        return $variant ? $this->variantFields->values($variant) : [];
    }

    public function normalize(array $data): array
    {
        foreach ($data as $field => $value) {
            $data[$field] = str_starts_with($field, AttributeSchema::PREFIX)
                ? $this->normalizeAttributeValue($value)
                : $this->variantFields->normalizeValue($field, $value);
        }

        return $data;
    }

    public function rules(Model $record): array
    {
        /** @var Product $record */
        if ($variant = $this->soleVariant($record)) {
            return $this->variantFields->rules($variant);
        }

        return array_fill_keys($this->variantFields->fields(), ['prohibited']);
    }

    public function commit(Model $record, array $values): void
    {
        /** @var Product $record */
        if ($values === [] || ! ($variant = $this->soleVariant($record))) {
            return;
        }

        $this->updatesProductVariant->execute(
            $variant,
            $this->variantFields->commitPayload($variant, $values),
        );
    }

    public function labels(): array
    {
        return $this->variantFields->labels();
    }

    protected function soleVariant(Product $record): ?ProductVariant
    {
        $variants = $record->variants()->limit(2)->get();

        return $variants->count() === 1 ? $variants->first() : null;
    }
}
