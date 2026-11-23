<?php

namespace Lunar\Panel\Sections\Catalog\Slices;

use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Contracts\Actions\Products\UpdatesProductVariant;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Panel\Drafts\Concerns\NormalizesDraftValues;
use Lunar\Panel\Forms\FormSlice;
use Lunar\Panel\Support\AttributeSchema;
use Lunar\Panel\Support\VariantFields;

/**
 * Simple-shape products (one variant, no options) edit their sole variant
 * inline, so its fields ride the product draft under `variant:` and keep a
 * single save cluster. Multi-variant products edit variants on their own
 * pages; here every variant field is refused outright. The variant's prices
 * ride along as `variant:price:{code}`, delegated to the variant page's own
 * price slice.
 */
class SoleVariantSlice extends FormSlice
{
    use NormalizesDraftValues;

    public const KEY = 'variant';

    public function __construct(
        protected UpdatesProductVariant $updatesProductVariant,
        protected VariantFields $variantFields,
        protected ProductVariantPriceSlice $priceSlice,
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
        /** @var Product $record */
        $variant = $this->soleVariant($record) ?? new ProductVariant;

        return [...$this->variantFields->fields(), ...$this->pricePrefixed($this->priceSlice->fields($variant))];
    }

    public function currentValues(Model $record): array
    {
        /** @var Product $record */
        $variant = $this->soleVariant($record);

        return $variant
            ? [...$this->variantFields->values($variant), ...$this->pricePrefixed($this->priceSlice->currentValues($variant))]
            : [];
    }

    public function normalize(array $data): array
    {
        [$prices, $data] = $this->splitPrices($data);

        foreach ($data as $field => $value) {
            $data[$field] = str_starts_with($field, AttributeSchema::PREFIX)
                ? $this->normalizeAttributeValue($value)
                : $this->variantFields->normalizeValue($field, $value);
        }

        return [...$data, ...$this->pricePrefixed($this->priceSlice->normalize($prices))];
    }

    public function rules(Model $record): array
    {
        /** @var Product $record */
        if ($variant = $this->soleVariant($record)) {
            return [...$this->variantFields->rules($variant), ...$this->pricePrefixed($this->priceSlice->rules($variant))];
        }

        return array_fill_keys($this->fields($record), ['prohibited']);
    }

    public function commit(Model $record, array $values): void
    {
        /** @var Product $record */
        if ($values === [] || ! ($variant = $this->soleVariant($record))) {
            return;
        }

        [$prices, $values] = $this->splitPrices($values);

        $this->updatesProductVariant->execute(
            $variant,
            $this->variantFields->commitPayload($variant, $values),
        );

        if ($prices !== [] && $this->priceSlice->normalize($prices) !== $this->priceSlice->normalize($this->priceSlice->currentValues($variant))) {
            $this->priceSlice->commit($variant, $prices);
        }
    }

    public function labels(): array
    {
        return [...$this->variantFields->labels(), ...$this->pricePrefixed($this->priceSlice->labels())];
    }

    /**
     * @param  array<string, mixed>|array<int, string>  $values
     * @return array<string, mixed>|array<int, string>
     */
    protected function pricePrefixed(array $values): array
    {
        $prefix = ProductVariantPriceSlice::KEY.':';

        if (array_is_list($values)) {
            return array_map(fn (string $field): string => $prefix.$field, $values);
        }

        return collect($values)->mapWithKeys(fn (mixed $value, string $key) => [$prefix.$key => $value])->all();
    }

    /**
     * Split `price:{code}` values (unprefixed) from the variant's own fields.
     *
     * @param  array<string, mixed>  $values
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    protected function splitPrices(array $values): array
    {
        $prefix = ProductVariantPriceSlice::KEY.':';
        $prices = [];

        foreach ($values as $key => $value) {
            if (str_starts_with($key, $prefix)) {
                $prices[substr($key, strlen($prefix))] = $value;
                unset($values[$key]);
            }
        }

        return [$prices, $values];
    }

    protected function soleVariant(Product $record): ?ProductVariant
    {
        $variants = $record->variants()->limit(2)->get();

        return $variants->count() === 1 ? $variants->first() : null;
    }
}
