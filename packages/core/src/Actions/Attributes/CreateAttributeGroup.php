<?php

namespace Lunar\Core\Actions\Attributes;

use Lunar\Core\Contracts\Actions\Attributes\CreatesAttributeGroup;
use Lunar\Core\Models\AttributeGroup;

class CreateAttributeGroup implements CreatesAttributeGroup
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): AttributeGroup
    {
        $attributes['position'] ??= ((int) AttributeGroup::query()->max('position')) + 1;

        return AttributeGroup::create($attributes);
    }
}
