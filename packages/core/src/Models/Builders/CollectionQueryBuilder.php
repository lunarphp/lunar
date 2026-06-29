<?php

namespace Lunar\Core\Models\Builders;

use Kalnoy\Nestedset\QueryBuilder;
use Lunar\Core\Models\Concerns\ResolvesRegisteredScopes;

/**
 * The nested-set builder for Collection, extended to also resolve
 * consumer-registered local scopes so `Collection::addLocalScope()` behaves the
 * same as on any other Lunar model.
 *
 * @template TModel of \Illuminate\Database\Eloquent\Model
 *
 * @extends QueryBuilder<TModel>
 */
class CollectionQueryBuilder extends QueryBuilder
{
    use ResolvesRegisteredScopes;
}
