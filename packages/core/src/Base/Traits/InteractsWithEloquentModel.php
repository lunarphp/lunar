<?php

namespace GetCandy\Base\Traits;

use GetCandy\Base\ModelFactory;
use Illuminate\Database\Eloquent\Model;
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
        $model = ModelFactory::getInstance()->getRegisteredModel(get_called_class());
        if (! in_array(get_called_class(), ModelFactory::getBaseModelClasses()) || ! $this->forwardCallsWhen($method, $model)) {
            return parent::__call($method, $parameters);
        }

        return $this->forwardCallTo($model, $method, $parameters);
    }

    /**
     * Forward a method call to the model only when calling a method on the model.
     *
     * @param  string  $method
     * @return bool
     */
    protected function forwardCallsWhen(string $method, Model $model): bool
    {
        $reflect = new \ReflectionClass($model);
        $methods = [];
        foreach ($reflect->getMethods() as $m) {
            if ($m->class == get_class($model)) {
                $methods[] = $m->name;
            }
        }

        return in_array($method, $methods);
    }
}
