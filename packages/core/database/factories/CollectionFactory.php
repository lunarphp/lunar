<?php

namespace GetCandy\Database\Factories;

use GetCandy\FieldTypes\Text;
use GetCandy\Models\Collection;
use GetCandy\Models\CollectionGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class CollectionFactory extends Factory
{
    protected $model = Collection::class;

    public function definition(): array
    {
        return [
            'collection_group_id' => CollectionGroup::factory(),
            'attribute_data'      => collect([
                'name' => new Text($this->faker->name),
            ]),
        ];
    }
}
