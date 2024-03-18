<?php

namespace Lunar\Database\Factories;

abstract class BaseFactory extends BaseFactory
{
    public function modelName()
    {
        return (new $this->model)::modelClass();
    }
}
