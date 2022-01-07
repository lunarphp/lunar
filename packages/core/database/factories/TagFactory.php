<?php

namespace GetCandy\Database\Factories;

use GetCandy\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TagFactory extends Factory
{
    protected $model = Tag::class;

    public function definition(): array
    {
        return [
            'value' => Str::upper($this->faker->word),
        ];
    }
}
