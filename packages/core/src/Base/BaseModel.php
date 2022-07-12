<?php

namespace GetCandy\Base;

use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
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
}
