<?php

namespace GetCandy\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Traits\Macroable;

abstract class BaseModel extends Model
{
    use Macroable {
        __call as macroCall;
    }

    /**
     * Create a new instance of the Model.
     *
     * @param  array  $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('getcandy.database.table_prefix').$this->getTable());

        if ($connection = config('getcandy.database.connection', false)) {
            $this->setConnection($connection);
        }
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        return parent::__call($method, $parameters);
    }

    /**
     * Handle dynamic static method calls into the model.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        return parent::__callStatic($method, $parameters);
    }
}
