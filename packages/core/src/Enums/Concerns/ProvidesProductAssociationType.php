<?php

namespace Lunar\Core\Enums\Concerns;

use Lunar\Core\Enums\ProductAssociation;

/**
 * @mixin ProductAssociation
 */
interface ProvidesProductAssociationType
{
    public function label(): string;
}
