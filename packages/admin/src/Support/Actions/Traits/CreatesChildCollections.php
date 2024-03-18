<?php

namespace Lunar\Admin\Support\Actions\Traits;

use Lunar\Models\Attribute;
use Lunar\Models\Collection;

trait CreatesChildCollections
{
    public function createChildCollection(Collection $parent, array $name): void
    {
        $attribute = Attribute::whereHandle('name')->whereAttributeType(Collection::class)->first()->type;

        $parent->appendNode(Collection::create([
            'collection_group_id' => $parent->collection_group_id,
            'attribute_data' => [
                'name' => new $attribute($name),
            ],
        ]));
    }
}
