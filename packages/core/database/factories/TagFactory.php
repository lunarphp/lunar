<?php

namespace Lunar\Database\Factories;

use Lunar\Models\Tag;

class TagFactory extends BaseFactory
{
    protected $model = Tag::class;

    public function definition(): array
    {
        return [
            'value' => $this->faker->word,
        ];
    }
}
