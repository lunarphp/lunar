<?php

namespace Lunar\Api\Storefront\Resources\V1;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Lunar\Api\Resources\Embed;
use Lunar\Api\Resources\Field;
use Lunar\Api\Resources\Filter;
use Lunar\Api\Resources\Resource;
use Lunar\Api\Resources\SerializationContext;
use Lunar\Api\Resources\Sort;
use Lunar\Core\Models\Collection;

class CollectionResource extends Resource
{
    public static function type(): string
    {
        return 'collections';
    }

    public static function model(): string
    {
        return Collection::class;
    }

    public function fields(): array
    {
        return [
            Field::translatable('name'),
            Field::make('handle'),
            Field::translatable('description'),
            Field::translatable('short_description'),
            Field::make('slug', fn (Collection $collection) => $collection->defaultUrl?->slug)->eagerLoad('defaultUrl'),
            Field::make('parent_id', fn (Collection $collection) => $collection->parent?->public_id)->eagerLoad('parent'),
            Field::make('group_id', fn (Collection $collection) => $collection->group?->public_id)->eagerLoad('group'),
            Field::make('attributes', fn (Collection $collection, SerializationContext $context) => collect($collection->attribute_data ?? [])
                ->map(fn ($field, string $handle) => $collection->translateAttribute($handle, $context->locale()))
                ->all()),
            Field::make('created_at'),
            Field::make('updated_at'),
        ];
    }

    public function includes(): array
    {
        return [
            Embed::relation('group', CollectionGroupResource::class),
            Embed::relation('parent', self::class),
            Embed::relation('children', self::class, constrain: fn ($query, SerializationContext $context) => self::visible($query, $context)),
            Embed::relation('products', ProductResource::class, constrain: fn ($query, SerializationContext $context) => ProductResource::visible($query, $context)),
            Embed::relation('urls', UrlResource::class),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::exact('id', 'public_id'),
            Filter::exact('handle'),
            Filter::make('group', fn (Builder $query, mixed $value) => $query->whereHas('group', fn ($group) => $group->whereIn('handle', Filter::listValue($value))))
                ->operators(['eq', 'in']),
            Filter::make('parent', fn (Builder $query, mixed $value) => $query->whereHas('parent', fn ($parent) => $parent->whereIn('public_id', Filter::listValue($value))))
                ->operators(['eq', 'in']),
            Filter::scope('root', 'whereIsRoot'),
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
        return self::visible(Collection::query(), $context);
    }

    public static function visible(Builder|Relation $query, SerializationContext $context): Builder|Relation
    {
        $query->whereVisible();

        if ($context->storefront) {
            $query->channel($context->storefront->channel)->customerGroup($context->storefront->customerGroups);
        }

        return $query;
    }
}
