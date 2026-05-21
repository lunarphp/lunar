<?php

namespace Lunar\Core\Base\Enums\Concerns;

use Lunar\Core\Base\Enums\ProductAssociation;

/**
 * @mixin ProductAssociation
 */
interface ProvidesProductAssociationType
{
    public function label(): string;
}
