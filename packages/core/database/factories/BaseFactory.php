<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

abstract class BaseFactory extends Factory
{
    public function modelName()
    {
        return $this->model;
    }
}
