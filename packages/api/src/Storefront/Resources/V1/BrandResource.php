<?php

namespace Lunar\Api\Storefront\Resources\V1;

use Illuminate\Database\Eloquent\Builder;
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

    public function fields(): array
    {
        return [
            Field::make('name'),
            Field::make('handle'),
            Field::translatable('description'),
            Field::translatable('short_description'),
            Field::make('slug', fn (Brand $brand) => $brand->defaultUrl?->slug)->eagerLoad('defaultUrl'),
            Field::make('attributes', fn (Brand $brand, SerializationContext $context) => collect($brand->attribute_data ?? [])
                ->map(fn ($field, string $handle) => $brand->translateAttribute($handle, $context->locale()))
                ->all()),
            Field::make('created_at'),
            Field::make('updated_at'),
        ];
    }

    public function includes(): array
    {
        return [
            Embed::relation('products', ProductResource::class, constrain: fn ($query, SerializationContext $context) => ProductResource::visible($query, $context)),
            Embed::relation('urls', UrlResource::class),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::exact('id', 'public_id'),
            Filter::exact('handle'),
        ];
    }

    public function sorts(): array
    {
        return [
            Sort::column('name'),
            Sort::column('created_at'),
        ];
    }

    public function query(SerializationContext $context): Builder
    {
        return Brand::query()->active();
    }
}
