<?php

namespace Lunar\Core\Contracts\Actions\Attributes;

use Lunar\Core\Models\AttributeGroup;

interface CreatesAttributeGroup
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): AttributeGroup;
}
