<?php

namespace Lunar\Api\Storefront\Resources\V1;

use ErrorException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Lunar\Api\OpenApi\Schema;
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

    public static function description(): string
    {
        return 'Sellable products with their lowest price for the request. Only published products scheduled into the request channel and visible to its customer groups are served.';
    }

    public function fields(): array
    {
        return [
            Field::translatable('name')->describe('The product name in the request locale.'),
            Field::translatable('description')->nullable()->describe('The long description in the request locale.'),
            Field::translatable('short_description')->nullable()->describe('The short description in the request locale.'),
            Field::make('slug', fn (Product $product) => $product->defaultUrl?->slug)->eagerLoad('defaultUrl')
                ->type(Schema::string()->nullable())->describe('The slug of the default URL, or null when the product has none.'),
            Field::make('product_type', fn (Product $product) => $product->productType?->name)->eagerLoad('productType')
                ->type(Schema::string()->nullable())->describe('Name of the product type.'),
            Field::make('brand_id', fn (Product $product) => $product->brand?->public_id)->eagerLoad('brand')
                ->type(Schema::string()->nullable())->describe('Public id of the brand, or null.'),
            Field::make('attributes', fn (Product $product, SerializationContext $context) => $this->attributes($product, $context))
                ->type(Schema::map(Schema::any()))->describe('Attribute values keyed by handle, translated into the request locale.'),
            Field::make('price', fn (Product $product, SerializationContext $context) => $this->lowestPrice($product, $context))
                ->eagerLoad(['variants.prices.currency', 'variants.prices.priceable'])
                ->type(Schema::money()->nullable())->describe('The lowest single-unit variant price for the request currency and customer groups, or null when no variant is priced.'),
            Field::make('created_at')->describe('When the product was created.'),
            Field::make('updated_at')->describe('When the product was last updated.'),
        ];
    }

    public function includes(): array
    {
        return [
            Embed::relation('brand', BrandResource::class)->describe('The brand, null when the product has none.'),
            Embed::relation('variants', ProductVariantResource::class)->describe('Every variant of the product.'),
            Embed::relation('collections', CollectionResource::class, constrain: fn ($query, SerializationContext $context) => CollectionResource::visible($query, $context))
                ->describe('Collections the product is in that are visible to the request.'),
            Embed::relation('urls', UrlResource::class)->describe('Every URL of the product.'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::exact('id', 'public_id')->describe('Match by public id.'),
            Filter::make('brand', fn (Builder $query, mixed $value) => $query->whereHas('brand', fn ($brand) => $brand->whereIn('handle', Filter::listValue($value))))
                ->operators(['eq', 'in'])->type(Schema::string())->describe('Products of the brand with this handle.'),
            Filter::make('collection', fn (Builder $query, mixed $value) => $query->whereHas('collections', fn ($collection) => $collection->whereIn('handle', Filter::listValue($value))))
                ->operators(['eq', 'in'])->type(Schema::string())->describe('Products in the collection with this handle.'),
            Filter::make('sku', fn (Builder $query, mixed $value, string $operator) => $query->whereHas('variants', fn ($variant) => Filter::applyToColumn($variant, 'sku', $value, $operator)))
                ->operators(['eq', 'in', 'like'])->type(Schema::string())->describe('Products with a variant matching this SKU.'),
            Filter::make('price', function (Builder $query, mixed $value, string $operator, SerializationContext $context): void {
                $query->whereHas('variants.prices', function ($price) use ($value, $operator, $context): void {
                    $price->where('currency_id', $context->storefront?->currency->id)
                        ->where('min_quantity', 1)
                        ->whereNull('customer_group_id');

                    Filter::applyToColumn($price, 'price', (int) $value, $operator);
                });
            })->operators(['eq', 'gt', 'gte', 'lt', 'lte'])->type(Schema::integer())->describe('Compare the single-unit base price in minor units of the request currency.'),
        ];
    }

    public function sorts(): array
    {
        return [
            Sort::column('created_at')->describe('By creation time.'),
            Sort::make('name', fn (Builder $query, string $direction, SerializationContext $context) => $query->orderBy($query->qualifyColumn('name').'->'.$context->locale(), $direction))
                ->describe('Alphabetical by name in the request locale.'),
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
