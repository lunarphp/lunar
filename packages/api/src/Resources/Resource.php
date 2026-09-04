<?php

namespace Lunar\Api\Resources;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * The unit of the API. Not Laravel's JsonResource: that class is bound to the
 * current request, and webhook payloads and console output must serialise a
 * model with no request in flight.
 */
abstract class Resource
{
    /** The wire type, e.g. `products`. */
    abstract public static function type(): string;

    /** @return class-string<Model> */
    abstract public static function model(): string;

    /** @return array<int, Field> */
    public function fields(): array
    {
        return [];
    }

    /** @return array<int, Embed> */
    public function includes(): array
    {
        return [];
    }

    /** @return array<int, Filter> */
    public function filters(): array
    {
        return [];
    }

    /** @return array<int, Sort> */
    public function sorts(): array
    {
        return [];
    }

    /**
     * Relations loaded on every index/show regardless of the request.
     *
     * @return array<int|string, mixed>
     */
    public function eagerLoad(): array
    {
        return [];
    }

    public function defaultPageSize(): int
    {
        return (int) config('lunar.api.pagination.default_size', 15);
    }

    public function maxPageSize(): int
    {
        return (int) config('lunar.api.pagination.max_size', 100);
    }

    /** Whether `?page[cursor]=` is accepted for this resource. */
    public function supportsCursorPagination(): bool
    {
        return false;
    }

    /**
     * The `id` a model is addressed by: `public_id`, or the immutable code for
     * models the public_id amendment excludes (currencies, languages, ...).
     */
    public function identifier(Model $model): string
    {
        return (string) ($model->getAttribute('public_id') ?? $model->getKey());
    }

    /**
     * Narrow a query to the model addressed by `$identifier`.
     *
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    public function scopeIdentifier(Builder $query, string $identifier): Builder
    {
        return $query->where($query->qualifyColumn('public_id'), $identifier);
    }

    /**
     * The base query for index/show. Surfaces override it to apply visibility
     * (published, in channel, for the customer groups) from the context.
     *
     * @return Builder<Model>
     */
    public function query(SerializationContext $context): Builder
    {
        return static::model()::query();
    }

    /**
     * Serialise a model, applying sparse fieldsets, requested includes and the
     * extensions registered against this resource.
     *
     * @return array<string, mixed>
     */
    final public function toArray(Model $model, SerializationContext $context): array
    {
        return $context->registry->definition(static::class)->serialize($model, $context);
    }
}
