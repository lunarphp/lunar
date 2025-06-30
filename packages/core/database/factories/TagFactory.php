<?php

namespace Lunar\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Models\Tag;

class TagFactory extends BaseFactory
{
    protected $model = Tag::class;

    public function definition(): array
    {
        return [
            'value' => Str::upper($this->faker->word),
        ];
    }
}
