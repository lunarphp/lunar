<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Core\Models\Promotion;

class PromotionFactory extends BaseFactory
{
    protected $model = Promotion::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);

        return [
            'public_id' => (string) Str::ulid(),
            'name' => ['en' => $name],
            'description' => null,
            'handle' => Str::slug($name),
            'starts_at' => null,
            'ends_at' => null,
        ];
    }
}
