<?php

namespace Lunar\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Lunar\Models\Language;

class LanguageFactory extends Factory
{
    protected $model = Language::class;

    public function definition(): array
    {
        return [
            'code'    => $this->faker->unique()->languageCode,
            'name'    => $this->faker->name(),
            'default' => true,
        ];
    }
}
