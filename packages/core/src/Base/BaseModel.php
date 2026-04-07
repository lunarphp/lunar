<?php

namespace Lunar\Base;

use Illuminate\Database\Eloquent\Model;
use Lunar\Base\Traits\HasModelExtending;

abstract class BaseModel extends Model
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
     * Compatibility shim for kalnoy/nestedset which calls whenBooted(),
     * a method only available in Laravel 12+. On Laravel 11, fall back
     * to booted() which runs at the same point in the boot lifecycle.
     */
    protected static function whenBooted(\Closure $callback): void
    {
        if (property_exists(\Illuminate\Database\Eloquent\Model::class, 'bootedCallbacks')) {
            parent::whenBooted($callback);
        } else {
            static::booted($callback);
        }
    }
}
