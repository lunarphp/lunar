<?php

namespace Lunar\Core\Contracts\Actions\Attributes;

use Lunar\Core\Models\AttributeGroup;

interface DeletesAttributeGroup
{
    public function execute(AttributeGroup $attributeGroup): void;
}
