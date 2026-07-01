<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Core\Models\Tag;

class TagFactory extends BaseFactory
{
    protected $model = Tag::class;

    public function definition(): array
    {
        return [
            'public_id' => (string) Str::ulid(),
            'value' => $this->faker->word,
        ];
    }
}
