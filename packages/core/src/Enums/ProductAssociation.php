<?php

namespace Lunar\Core\Enums;

use Lunar\Core\Enums\Concerns\ProvidesProductAssociationType;

enum ProductAssociation: string implements ProvidesProductAssociationType
{
    case CROSS_SELL = 'cross-sell';
    case UP_SELL = 'up-sell';
    case ALTERNATE = 'alternate';

    public function label(): string
    {
        return match ($this) {
            self::CROSS_SELL => __('lunar::base.product-association-types.cross-sell'),
            self::UP_SELL => __('lunar::base.product-association-types.up-sell'),
            self::ALTERNATE => __('lunar::base.product-association-types.alternate'),
        };
    }
}
