<?php

namespace Lunar\Api\Admin\Resources\V1;

use Illuminate\Database\Eloquent\Builder;
use Lunar\Api\OpenApi\Schema;
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

    public static function description(): string
    {
        return 'Every product in the catalogue, whatever its state, with raw attribute data and locale maps.';
    }

    public function fields(): array
    {
        return [
            Field::translatable('name')->describe('The product name, keyed by locale.'),
            Field::translatable('description')->nullable()->describe('The long description, keyed by locale.'),
            Field::translatable('short_description')->nullable()->describe('The short description, keyed by locale.'),
            Field::make('status')->describe('Lifecycle state: draft, published or archived.'),
            Field::make('brand_id', fn (Product $product) => $product->brand?->public_id)->eagerLoad('brand')
                ->type(Schema::string()->nullable())->describe('Public id of the brand, or null.'),
            Field::make('product_type_id', fn (Product $product) => $product->productType?->public_id)->eagerLoad('productType')
                ->type(Schema::string()->nullable())->describe('Public id of the product type.'),
            Field::make('attribute_data', fn (Product $product) => collect($product->attribute_data ?? [])
                ->map(fn ($field) => $field instanceof FieldType ? $field->getValue() : $field)
                ->all())
                ->type(Schema::map(Schema::any()))->describe('Raw attribute values keyed by handle; translatable attributes are locale maps.'),
            Field::make('created_at')->describe('When the product was created.'),
            Field::make('updated_at')->describe('When the product was last updated.'),
        ];
    }

    public function includes(): array
    {
        return [
            Embed::relation('brand', BrandResource::class)->describe('The brand, null when the product has none.'),
            Embed::relation('variants', ProductVariantResource::class)->describe('Every variant of the product.'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::exact('id', 'public_id')->describe('Match by public id.'),
            Filter::exact('status')->describe('Match by lifecycle state.'),
            Filter::make('brand', fn (Builder $query, mixed $value) => $query->whereHas('brand', fn ($brand) => $brand->whereIn('public_id', Filter::listValue($value))))
                ->operators(['eq', 'in'])->type(Schema::string())->describe('Products of the brand with this public id.'),
            Filter::make('sku', fn (Builder $query, mixed $value, string $operator) => $query->whereHas('variants', fn ($variant) => Filter::applyToColumn($variant, 'sku', $value, $operator)))
                ->operators(['eq', 'in', 'like'])->type(Schema::string())->describe('Products with a variant matching this SKU.'),
            Filter::column('updated_at')->operators(['gt', 'gte', 'lt', 'lte'])->describe('Products changed before or after an instant, for incremental sync.'),
        ];
    }

    public function sorts(): array
    {
        return [
            Sort::column('created_at')->describe('By creation time.'),
            Sort::column('updated_at')->describe('By last change.'),
        ];
    }
}
