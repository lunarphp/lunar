<?php

namespace Lunar\Api\Storefront\Resources\V1;

use Illuminate\Database\Eloquent\Builder;
use Lunar\Api\OpenApi\Schema;
use Lunar\Api\Resources\Embed;
use Lunar\Api\Resources\Field;
use Lunar\Api\Resources\Filter;
use Lunar\Api\Resources\Resource;
use Lunar\Api\Resources\SerializationContext;
use Lunar\Api\Resources\Sort;
use Lunar\Core\Models\Brand;

class BrandResource extends Resource
{
    public static function type(): string
    {
        return 'brands';
    }

    public static function model(): string
    {
        return Brand::class;
    }

    public static function description(): string
    {
        return 'Brands group products under a manufacturer or label. Only active brands are served.';
    }

    public function fields(): array
    {
        return [
            Field::make('name')->describe('The brand name.'),
            Field::make('handle')->describe('The unique handle, used by the handle filter.'),
            Field::translatable('description')->nullable()->describe('The long description in the request locale.'),
            Field::translatable('short_description')->nullable()->describe('The short description in the request locale.'),
            Field::make('slug', fn (Brand $brand) => $brand->defaultUrl?->slug)->eagerLoad('defaultUrl')
                ->type(Schema::string()->nullable())->describe('The slug of the default URL, or null when the brand has none.'),
            Field::make('attributes', fn (Brand $brand, SerializationContext $context) => collect($brand->attribute_data ?? [])
                ->map(fn ($field, string $handle) => $brand->translateAttribute($handle, $context->locale()))
                ->all())
                ->type(Schema::map(Schema::any()))->describe('Attribute values keyed by handle, translated into the request locale.'),
            Field::make('created_at')->describe('When the brand was created.'),
            Field::make('updated_at')->describe('When the brand was last updated.'),
        ];
    }

    public function includes(): array
    {
        return [
            Embed::relation('products', ProductResource::class, constrain: fn ($query, SerializationContext $context) => ProductResource::visible($query, $context))
                ->describe('Products of the brand visible to the request.'),
            Embed::relation('urls', UrlResource::class)->describe('Every URL of the brand.'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::exact('id', 'public_id')->describe('Match by public id.'),
            Filter::exact('handle')->describe('Match by handle.'),
        ];
    }

    public function sorts(): array
    {
        return [
            Sort::column('name')->describe('Alphabetical by name.'),
            Sort::column('created_at')->describe('By creation time.'),
        ];
    }

    public function query(SerializationContext $context): Builder
    {
        return Brand::query()->active();
    }
}
