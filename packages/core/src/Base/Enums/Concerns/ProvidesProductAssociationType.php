<?php

namespace Lunar\Base\Enums\Concerns;

use Lunar\Base\Enums\ProductAssociation;

/**
 * @mixin ProductAssociation
 */
interface ProvidesProductAssociationType
{
    public function label(): string;
}
