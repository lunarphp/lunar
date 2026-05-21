<?php

namespace Lunar\Admin\Support\Actions\Traits;

use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Contracts\Collection as CollectionContract;

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
