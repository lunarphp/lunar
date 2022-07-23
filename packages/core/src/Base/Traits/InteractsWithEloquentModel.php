<?php

namespace GetCandy\Base\Traits;

use GetCandy\Base\ModelFactory;
use Illuminate\Support\Traits\ForwardsCalls;

trait InteractsWithEloquentModel
{
    use ForwardsCalls;

    /**
     * Handle dynamic and static method calls into the model.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (!in_array(get_called_class(), ModelFactory::getBaseModelClasses())) {
            return parent::__call($method, $parameters);
        }

        $model = ModelFactory::getInstance()->getRegisteredModel(get_called_class());
        return $this->forwardCallTo($model, $method, $parameters);
    }
}
