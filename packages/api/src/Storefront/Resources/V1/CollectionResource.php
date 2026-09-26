<?php

namespace Lunar\Api\Storefront\Resources\V1;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Lunar\Api\OpenApi\Schema;
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

    public static function description(): string
    {
        return 'Nested groupings of products, such as categories. Only collections visible in the request channel and customer groups are served.';
    }

    public function fields(): array
    {
        return [
            Field::translatable('name')->describe('The collection name in the request locale.'),
            Field::make('handle')->describe('The unique handle, used by the handle filter.'),
            Field::translatable('description')->nullable()->describe('The long description in the request locale.'),
            Field::translatable('short_description')->nullable()->describe('The short description in the request locale.'),
            Field::make('slug', fn (Collection $collection) => $collection->defaultUrl?->slug)->eagerLoad('defaultUrl')
                ->type(Schema::string()->nullable())->describe('The slug of the default URL, or null when the collection has none.'),
            Field::make('parent_id', fn (Collection $collection) => $collection->parent?->public_id)->eagerLoad('parent')
                ->type(Schema::string()->nullable())->describe('Public id of the parent collection; null for a root collection.'),
            Field::make('group_id', fn (Collection $collection) => $collection->group?->public_id)->eagerLoad('group')
                ->type(Schema::string()->nullable())->describe('Public id of the collection group.'),
            Field::make('attributes', fn (Collection $collection, SerializationContext $context) => collect($collection->attribute_data ?? [])
                ->map(fn ($field, string $handle) => $collection->translateAttribute($handle, $context->locale()))
                ->all())
                ->type(Schema::map(Schema::any()))->describe('Attribute values keyed by handle, translated into the request locale.'),
            Field::make('created_at')->describe('When the collection was created.'),
            Field::make('updated_at')->describe('When the collection was last updated.'),
        ];
    }

    public function includes(): array
    {
        return [
            Embed::relation('group', CollectionGroupResource::class)->describe('The group the collection belongs to.'),
            Embed::relation('parent', self::class)->describe('The parent collection, null for a root.'),
            Embed::relation('children', self::class, constrain: fn ($query, SerializationContext $context) => self::visible($query, $context))
                ->describe('Child collections visible to the request.'),
            Embed::relation('products', ProductResource::class, constrain: fn ($query, SerializationContext $context) => ProductResource::visible($query, $context))
                ->describe('Products in the collection visible to the request.'),
            Embed::relation('urls', UrlResource::class)->describe('Every URL of the collection.'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::exact('id', 'public_id')->describe('Match by public id.'),
            Filter::exact('handle')->describe('Match by handle.'),
            Filter::make('group', fn (Builder $query, mixed $value) => $query->whereHas('group', fn ($group) => $group->whereIn('handle', Filter::listValue($value))))
                ->operators(['eq', 'in'])->type(Schema::string())->describe('Collections in the group with this handle.'),
            Filter::make('parent', fn (Builder $query, mixed $value) => $query->whereHas('parent', fn ($parent) => $parent->whereIn('public_id', Filter::listValue($value))))
                ->operators(['eq', 'in'])->type(Schema::string())->describe('Children of the collection with this public id.'),
            Filter::scope('root', 'whereIsRoot')->describe('Only root collections when true.'),
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
