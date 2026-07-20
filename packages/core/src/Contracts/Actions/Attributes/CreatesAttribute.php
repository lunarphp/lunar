<?php

namespace Lunar\Core\Contracts\Actions\Attributes;

use Lunar\Core\Models\Attribute;

interface CreatesAttribute
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): Attribute;
}
