<?php

namespace Lunar\Core\Database\Factories;

use Lunar\Core\Models\Tag;

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
