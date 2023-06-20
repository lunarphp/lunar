<?php

namespace Lunar\Base;

use Illuminate\Database\Eloquent\Model;
use Lunar\Base\Traits\HasModelExtending;
use Spatie\LaravelBlink\BlinkFacade as Blink;

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

        if ($connection = config('lunar.database.connection', false)) {
            $this->setConnection($connection);
        }
    }
    
    protected function getCachedRelation($attribute, $callback, $morphType = '')
    {
        return Blink::once('lunar:'.$attribute.$morphType.':'.$this->{$attribute}, function() use ($callback) {
            return $callback();    
        });
    }
}
