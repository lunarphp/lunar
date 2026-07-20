<?php

namespace Lunar\Core\Contracts\Actions\Attributes;

use Lunar\Core\Models\Attribute;

interface UpdatesAttribute
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(Attribute $attribute, array $attributes): Attribute;
}
