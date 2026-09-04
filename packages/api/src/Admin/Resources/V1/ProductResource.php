<?php

namespace Lunar\Api\Admin\Resources\V1;

use Illuminate\Database\Eloquent\Builder;
use Lunar\Api\Resources\Embed;
use Lunar\Api\Resources\Field;
use Lunar\Api\Resources\Filter;
use Lunar\Api\Resources\Resource;
use Lunar\Api\Resources\Sort;
use Lunar\Core\Contracts\FieldType;
use Lunar\Core\Models\Product;

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
            Field::make('status'),
            Field::make('brand_id', fn (Product $product) => $product->brand?->public_id)->eagerLoad('brand'),
            Field::make('product_type_id', fn (Product $product) => $product->productType?->public_id)->eagerLoad('productType'),
            Field::make('attribute_data', fn (Product $product) => collect($product->attribute_data ?? [])
                ->map(fn ($field) => $field instanceof FieldType ? $field->getValue() : $field)
                ->all()),
            Field::make('created_at'),
            Field::make('updated_at'),
        ];
    }

    public function includes(): array
    {
        return [
            Embed::relation('brand', BrandResource::class),
            Embed::relation('variants', ProductVariantResource::class),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::exact('id', 'public_id'),
            Filter::exact('status'),
            Filter::make('brand', fn (Builder $query, mixed $value) => $query->whereHas('brand', fn ($brand) => $brand->whereIn('public_id', Filter::listValue($value))))
                ->operators(['eq', 'in']),
            Filter::make('sku', fn (Builder $query, mixed $value, string $operator) => $query->whereHas('variants', fn ($variant) => Filter::applyToColumn($variant, 'sku', $value, $operator)))
                ->operators(['eq', 'in', 'like']),
            Filter::column('updated_at')->operators(['gt', 'gte', 'lt', 'lte']),
        ];
    }

    public function sorts(): array
    {
        return [
            Sort::column('created_at'),
            Sort::column('updated_at'),
        ];
    }
}
