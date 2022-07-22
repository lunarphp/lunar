<?php

namespace GetCandy\Base\Traits;

use GetCandy\Models\AttributeGroup;

trait WithModelAttributeGroup
{
    /**
     * Get attribute group from model and override attributes from source.
     *
     * @param  \GetCandy\Models\AttributeGroup  $group
     * @return \GetCandy\Models\AttributeGroup
     */
    protected function getAttributeGroupFromModel(AttributeGroup $group): AttributeGroup
    {
        if (! $group->source) {
            return $group;
        }

        try {
            /** @var \Illuminate\Database\Eloquent\Model $model */
            $model = app($group->source);
            $group->attributes = $model::all();
        } catch (\Exception $e) {
            //dd($e->getMessage());
        }

        return $group;
    }
}
