<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Attributes\CollectedBy;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\Concerns\HasModelExtending;

abstract class Base extends Model
{
    use HasModelExtending;

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
