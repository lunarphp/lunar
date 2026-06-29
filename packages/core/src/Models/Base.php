<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Attributes\CollectedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

abstract class Base extends Model
{
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
