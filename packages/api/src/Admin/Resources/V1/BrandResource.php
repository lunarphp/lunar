<?php

namespace Lunar\Api\Admin\Resources\V1;

use Lunar\Api\Resources\Field;
use Lunar\Api\Resources\Filter;
use Lunar\Api\Resources\Resource;
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
            Field::make('status'),
            Field::translatable('description'),
            Field::translatable('short_description'),
            Field::make('created_at'),
            Field::make('updated_at'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::exact('id', 'public_id'),
            Filter::exact('handle'),
            Filter::exact('status'),
        ];
    }

    public function sorts(): array
    {
        return [
            Sort::column('name'),
            Sort::column('created_at'),
        ];
    }
}
