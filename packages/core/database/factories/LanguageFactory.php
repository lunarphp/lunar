<?php

namespace GetCandy\Database\Factories;

use GetCandy\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LanguageFactory extends Factory
{
    protected $model = Language::class;

    public function definition(): array
    {
        return [
            'code' => Str::random(2),
            'name' => $this->faker->name(),
            'default' => true,
        ];
    }
}
