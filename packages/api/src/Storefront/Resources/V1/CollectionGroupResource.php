<?php

namespace Lunar\Api\Storefront\Resources\V1;

use Lunar\Api\Resources\Embed;
use Lunar\Api\Resources\Field;
use Lunar\Api\Resources\Filter;
use Lunar\Api\Resources\Resource;
use Lunar\Api\Resources\SerializationContext;
use Lunar\Api\Resources\Sort;
use Lunar\Core\Models\CollectionGroup;

class CollectionGroupResource extends Resource
{
    public static function type(): string
    {
        return 'collection-groups';
    }

    public static function model(): string
    {
        return CollectionGroup::class;
    }

    public function fields(): array
    {
        return [
            Field::make('name'),
            Field::make('handle'),
            Field::make('created_at'),
            Field::make('updated_at'),
        ];
    }

    public function includes(): array
    {
        return [
            Embed::relation('collections', CollectionResource::class, constrain: fn ($query, SerializationContext $context) => CollectionResource::visible($query, $context)),
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
}
