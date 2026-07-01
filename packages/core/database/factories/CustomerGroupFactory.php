<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Core\Models\CustomerGroup;

class CustomerGroupFactory extends BaseFactory
{
    protected $model = CustomerGroup::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->word;

        return [
            'public_id' => (string) Str::ulid(),
            'name' => $name,
            'handle' => Str::slug($name),
        ];
    }
}
