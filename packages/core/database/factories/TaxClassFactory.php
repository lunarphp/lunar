<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Core\Models\TaxClass;

class TaxClassFactory extends BaseFactory
{
    protected $model = TaxClass::class;

    public function definition(): array
    {
        return [
            'public_id' => (string) Str::ulid(),
            'name' => $this->faker->name,
            'default' => false,
        ];
    }
}
