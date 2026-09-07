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

    public static function description(): string
    {
        return 'Named sets of collections, such as a main menu or a seasonal campaign.';
    }

    public function fields(): array
    {
        return [
            Field::make('name')->describe('The group name.'),
            Field::make('handle')->describe('The unique handle, used by the handle filter.'),
            Field::make('created_at')->describe('When the group was created.'),
            Field::make('updated_at')->describe('When the group was last updated.'),
        ];
    }

    public function includes(): array
    {
        return [
            Embed::relation('collections', CollectionResource::class, constrain: fn ($query, SerializationContext $context) => CollectionResource::visible($query, $context))
                ->describe('Collections in the group visible to the request.'),
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
}
