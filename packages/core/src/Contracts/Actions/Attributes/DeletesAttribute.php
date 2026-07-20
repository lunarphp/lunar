<?php

namespace Lunar\Core\Contracts\Actions\Attributes;

use Lunar\Core\Models\Attribute;

interface DeletesAttribute
{
    public function execute(Attribute $attribute): void;
}
