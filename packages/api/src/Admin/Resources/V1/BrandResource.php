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

    public static function description(): string
    {
        return 'Every brand in the catalogue, whatever its state.';
    }

    public function fields(): array
    {
        return [
            Field::make('name')->describe('The brand name.'),
            Field::make('handle')->describe('The unique handle.'),
            Field::make('status')->describe('Lifecycle state, such as active or archived.'),
            Field::translatable('description')->nullable()->describe('The long description, keyed by locale.'),
            Field::translatable('short_description')->nullable()->describe('The short description, keyed by locale.'),
            Field::make('created_at')->describe('When the brand was created.'),
            Field::make('updated_at')->describe('When the brand was last updated.'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::exact('id', 'public_id')->describe('Match by public id.'),
            Filter::exact('handle')->describe('Match by handle.'),
            Filter::exact('status')->describe('Match by lifecycle state.'),
        ];
    }

    public function sorts(): array
    {
        return [
            Sort::column('name')->describe('Alphabetical by name.'),
            Sort::column('created_at')->describe('By creation time.'),
        ];
    }
}
