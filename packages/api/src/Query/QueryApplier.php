<?php

namespace Lunar\Api\Query;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Lunar\Api\Registry\ResourceDefinition;
use Lunar\Api\Resources\SerializationContext;

/**
 * Applies a parsed query to an Eloquent builder: eager loads for every field
 * and include that will serialise (recursively, so nested includes never
 * lazy-load), then filters, sorts and pagination.
 */
final class QueryApplier
{
    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    public function apply(Builder $query, ResourceDefinition $definition, Query $parsed, SerializationContext $context): Builder
    {
        $this->applyEagerLoads($query, $definition, $context);

        foreach ($parsed->filters as ['filter' => $filter, 'operator' => $operator, 'value' => $value]) {
            $filter->apply($query, $value, $operator, $context);
        }

        foreach ($parsed->sorts as ['sort' => $sort, 'direction' => $direction]) {
            $sort->apply($query, $direction, $context);
        }

        // A deterministic order keeps pages stable and cursor pagination possible.
        $query->orderBy($query->qualifyColumn($query->getModel()->getKeyName()));

        return $query;
    }

    /**
     * @param  Builder<Model>|Relation<Model, Model, mixed>  $query
     */
    public function applyEagerLoads(Builder|Relation $query, ResourceDefinition $definition, SerializationContext $context): void
    {
        if ($definition->eagerLoad() !== []) {
            $query->with($definition->eagerLoad());
        }

        $requested = $context->fieldsFor($definition->type());

        foreach ($definition->fields() as $name => $field) {
            if ($requested !== null && ! in_array($name, $requested, true)) {
                continue;
            }

            if ($field->visibleTo($context)) {
                $field->applyEagerLoads($query);
            }
        }

        foreach ($context->includes->names() as $name) {
            $include = $definition->embed($name);

            if (! $include || ! $include->visibleTo($context)) {
                continue;
            }

            $related = $context->registry->definition($include->resource);
            $child = $context->descend($name);
            $constraint = $include->constraint();
            $nestedRelation = $include->relationName();

            foreach ($include->eagerLoads() as $relation) {
                $query->with([$relation => function (Relation $relationQuery) use ($constraint, $relation, $nestedRelation, $related, $child, $context): void {
                    if ($constraint) {
                        $constraint($relationQuery, $context);
                    }

                    if ($relation === $nestedRelation) {
                        $this->applyEagerLoads($relationQuery, $related, $child);
                    }
                }]);
            }
        }
    }

    /**
     * @param  Builder<Model>  $query
     */
    public function paginate(Builder $query, ResourceDefinition $definition, Query $parsed): LengthAwarePaginator|CursorPaginator
    {
        if ($parsed->cursor !== null) {
            return $query->cursorPaginate($parsed->pageSize, ['*'], 'page[cursor]', $parsed->cursor);
        }

        return $query->paginate($parsed->pageSize, ['*'], 'page[number]', $parsed->pageNumber ?? 1);
    }
}
