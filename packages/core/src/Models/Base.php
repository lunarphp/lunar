<?php

namespace Lunar\Core\Models;

use Closure;
use Illuminate\Database\Eloquent\Attributes\CollectedBy;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Lunar\Core\Models\Builders\Builder;
use Lunar\Core\Models\Concerns\HasExtendableCasts;
use Lunar\Core\Models\Relations\BelongsToMany;
use Lunar\Core\Models\Relations\MorphToMany;

abstract class Base extends Model
{
    use HasExtendableCasts;

    /**
     * Create a new instance of the Model.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('lunar.database.table_prefix').$this->getTable());

        if ($connection = config('lunar.database.connection')) {
            $this->setConnection($connection);
        }
    }

    /**
     * The model's morph map alias (e.g. "product"), mirroring getMorphClass()
     * without needing an instance.
     */
    public static function morphName(): string
    {
        return array_search(static::class, Relation::morphMap(), true) ?: static::class;
    }

    /**
     * Register an optional, named local scope on this model from outside the
     * class (e.g. a service provider). Once registered it is callable exactly
     * like a native local scope: `Product::featured()`, `->featured()`.
     */
    public static function addLocalScope(string $name, Closure $scope): void
    {
        Builder::registerScope(static::class, $name, $scope);
    }

    /**
     * {@inheritdoc}
     *
     * @return Builder<static>
     */
    public function newEloquentBuilder($query): EloquentBuilder
    {
        return new Builder($query);
    }

    /**
     * {@inheritdoc}
     *
     * Return a relation that records cache invalidation on native pivot writes
     * (a no-op for models that do not participate in cache invalidation).
     */
    protected function newBelongsToMany(EloquentBuilder $query, Model $parent, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey, $relationName = null)
    {
        return new BelongsToMany($query, $parent, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey, $relationName);
    }

    /**
     * {@inheritdoc}
     */
    protected function newMorphToMany(EloquentBuilder $query, Model $parent, $name, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey, $relationName = null, $inverse = false)
    {
        return new MorphToMany($query, $parent, $name, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey, $relationName, $inverse);
    }

    /**
     * {@inheritdoc}
     */
    public function resolveCollectionFromAttribute()
    {
        $reflectionClass = new \ReflectionClass(static::class);

        $attributes = $reflectionClass->getAttributes(
            CollectedBy::class
        );

        if (! isset($attributes[0]) || ! isset($attributes[0]->getArguments()[0])) {
            return null;
        }

        return $attributes[0]->getArguments()[0];
    }
}
