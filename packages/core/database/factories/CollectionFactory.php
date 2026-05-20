<?php

namespace Lunar\Core\Database\Factories;

use Lunar\Core\FieldTypes\Text;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\CollectionGroup;

class CollectionFactory extends BaseFactory
{
    protected $model = Collection::class;

    public function definition(): array
    {
        return [
            'collection_group_id' => CollectionGroup::factory(),
            'attribute_data' => collect([
                'name' => new Text($this->faker->name),
            ]),
        ];
    }
}
