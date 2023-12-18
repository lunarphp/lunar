<?php

namespace Lunar\Admin\Support\Actions\Traits;

use Lunar\Facades\DB;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\Attribute;
use Lunar\Models\Collection;
use Lunar\Models\Language;

trait CreatesChildCollections
{
    public function createChildCollection(Collection $parent, string $name)
    {
        DB::beginTransaction();

        $attribute = Attribute::whereHandle('name')->whereAttributeType(Collection::class)->first();

        $nameValue = $name;
        $fieldType = $attribute->type;

        if ($fieldType == TranslatedText::class) {
            $language = Language::getDefault();
            $nameValue = collect([
                $language->code => $name,
            ]);
        }

        Collection::create([
            'collection_group_id' => $parent->collection_group_id,
            'attribute_data' => [
                'name' => new $fieldType($nameValue),
            ],
        ], $parent);

        DB::commit();
    }
}
