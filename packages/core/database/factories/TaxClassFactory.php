<?php

namespace Lunar\Core\Database\Factories;

use Lunar\Core\Models\TaxClass;

class TaxClassFactory extends BaseFactory
{
    protected $model = TaxClass::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'default' => false,
        ];
    }
}
