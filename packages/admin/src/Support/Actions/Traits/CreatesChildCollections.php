<?php

namespace Lunar\Admin\Support\Actions\Traits;

use Lunar\Facades\DB;
use Lunar\Models\Attribute;
use Lunar\Models\Collection;
use Lunar\Models\Contracts\Collection as CollectionContract;

trait CreatesChildCollections
{
    public function createChildCollection(CollectionContract $parent, array|string $name)
    {
        DB::beginTransaction();

        $attribute = Attribute::whereHandle('name')->whereAttributeType(
            Collection::morphName()
        )->first()->type;

        $parent->appendNode(Collection::create([
            'collection_group_id' => $parent->collection_group_id,
            'attribute_data' => [
                'name' => new $attribute($name),
            ],
        ]));

        DB::commit();
    }
}
