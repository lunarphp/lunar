<?php

namespace Lunar\Api\Storefront\Resources\V1;

use ErrorException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Lunar\Api\Resources\Embed;
use Lunar\Api\Resources\Field;
use Lunar\Api\Resources\Filter;
use Lunar\Api\Resources\Resource;
use Lunar\Api\Resources\SerializationContext;
use Lunar\Api\Resources\Sort;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Exceptions\MissingCurrencyPriceException;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;

class ProductResource extends Resource
{
    public static function type(): string
    {
        return 'products';
    }

    public static function model(): string
    {
        return Product::class;
    }

    public function fields(): array
    {
        return [
            Field::translatable('name'),
            Field::translatable('description'),
            Field::translatable('short_description'),
            Field::make('slug', fn (Product $product) => $product->defaultUrl?->slug)->eagerLoad('defaultUrl'),
            Field::make('product_type', fn (Product $product) => $product->productType?->name)->eagerLoad('productType'),
            Field::make('brand_id', fn (Product $product) => $product->brand?->public_id)->eagerLoad('brand'),
            Field::make('attributes', fn (Product $product, SerializationContext $context) => $this->attributes($product, $context)),
            Field::make('price', fn (Product $product, SerializationContext $context) => $this->lowestPrice($product, $context))
                ->eagerLoad(['variants.prices.currency', 'variants.prices.priceable']),
            Field::make('created_at'),
            Field::make('updated_at'),
        ];
    }

    public function includes(): array
    {
        return [
            Embed::relation('brand', BrandResource::class),
            Embed::relation('variants', ProductVariantResource::class),
            Embed::relation('collections', CollectionResource::class, constrain: fn ($query, SerializationContext $context) => CollectionResource::visible($query, $context)),
            Embed::relation('urls', UrlResource::class),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::exact('id', 'public_id'),
            Filter::make('brand', fn (Builder $query, mixed $value) => $query->whereHas('brand', fn ($brand) => $brand->whereIn('handle', Filter::listValue($value))))
                ->operators(['eq', 'in']),
            Filter::make('collection', fn (Builder $query, mixed $value) => $query->whereHas('collections', fn ($collection) => $collection->whereIn('handle', Filter::listValue($value))))
                ->operators(['eq', 'in']),
            Filter::make('sku', fn (Builder $query, mixed $value, string $operator) => $query->whereHas('variants', fn ($variant) => Filter::applyToColumn($variant, 'sku', $value, $operator)))
                ->operators(['eq', 'in', 'like']),
            Filter::make('price', function (Builder $query, mixed $value, string $operator, SerializationContext $context): void {
                $query->whereHas('variants.prices', function ($price) use ($value, $operator, $context): void {
                    $price->where('currency_id', $context->storefront?->currency->id)
                        ->where('min_quantity', 1)
                        ->whereNull('customer_group_id');

                    Filter::applyToColumn($price, 'price', (int) $value, $operator);
                });
            })->operators(['eq', 'gt', 'gte', 'lt', 'lte']),
        ];
    }

    public function sorts(): array
    {
        return [
            Sort::column('created_at'),
            Sort::make('name', fn (Builder $query, string $direction, SerializationContext $context) => $query->orderBy($query->qualifyColumn('name').'->'.$context->locale(), $direction)),
        ];
    }

    public function query(SerializationContext $context): Builder
    {
        return self::visible(Product::query(), $context);
    }

    /**
     * Published, scheduled into the context's channel and visible to its
     * customer groups.
     */
    public static function visible(Builder|Relation $query, SerializationContext $context): Builder|Relation
    {
        $query->whereVisible();

        if ($context->storefront) {
            $query->channel($context->storefront->channel)->customerGroup($context->storefront->customerGroups);
        }

        return $query;
    }

    /** @return array<string, mixed> */
    protected function attributes(Product $product, SerializationContext $context): array
    {
        return collect($product->attribute_data ?? [])
            ->map(fn ($field, string $handle) => $product->translateAttribute($handle, $context->locale()))
            ->all();
    }

    protected function lowestPrice(Product $product, SerializationContext $context): ?PriceValue
    {
        $lowest = null;

        foreach ($product->variants as $variant) {
            $price = self::variantPrice($variant, $context);

            if ($price && ($lowest === null || $price->value < $lowest->value)) {
                $lowest = $price;
            }
        }

        return $lowest;
    }

    public static function variantPrice(ProductVariant $variant, SerializationContext $context): ?PriceValue
    {
        try {
            $matched = $variant->pricing($context->storefront)->get()->matched;
        } catch (MissingCurrencyPriceException|ErrorException) {
            return null;
        }

        return new PriceValue($matched->price, $matched->currency);
    }
}
