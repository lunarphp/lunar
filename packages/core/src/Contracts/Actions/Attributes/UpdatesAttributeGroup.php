<?php

namespace Lunar\Core\Contracts\Actions\Attributes;

use Lunar\Core\Models\AttributeGroup;

interface UpdatesAttributeGroup
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(AttributeGroup $attributeGroup, array $attributes): AttributeGroup;
}
