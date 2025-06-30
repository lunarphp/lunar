<?php

namespace Lunar\Database\Factories;

use Lunar\Models\Language;

class LanguageFactory extends BaseFactory
{
    protected $model = Language::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->slug(),
            'name' => $this->faker->name(),
            'default' => true,
        ];
    }
}
