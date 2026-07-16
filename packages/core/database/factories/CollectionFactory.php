<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\CollectionGroup;

class CollectionFactory extends BaseFactory
{
    protected $model = Collection::class;

    public function definition(): array
    {
        return [
            'public_id' => (string) Str::ulid(),
            'collection_group_id' => CollectionGroup::factory(),
            'name' => collect(['en' => $this->faker->words(3, true)]),
            'description' => collect(['en' => $this->faker->paragraph]),
            'short_description' => collect(['en' => $this->faker->sentence]),
            'attribute_data' => collect(),
        ];
    }
}
