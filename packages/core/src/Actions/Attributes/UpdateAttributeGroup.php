<?php

namespace Lunar\Core\Actions\Attributes;

use Lunar\Core\Contracts\Actions\Attributes\UpdatesAttributeGroup;
use Lunar\Core\Models\AttributeGroup;

class UpdateAttributeGroup implements UpdatesAttributeGroup
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(AttributeGroup $attributeGroup, array $attributes): AttributeGroup
    {
        $attributeGroup->update($attributes);

        return $attributeGroup;
    }
}
