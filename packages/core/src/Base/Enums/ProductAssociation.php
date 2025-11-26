<?php

namespace Lunar\Base\Enums;


use Lunar\Base\Enums\Concerns\ProvidesProductAssociationType;

enum ProductAssociation: string implements ProvidesProductAssociationType
{
    case CROSS_SELL = 'cross-sell';
    case UP_SELL = 'up-sell';
    case ALTERNATE = 'alternate';

    public function label(): string
    {
        return match ($this) {
            self::CROSS_SELL => 'Cross Sell',
            self::UP_SELL => 'Up Sell',
            self::ALTERNATE => 'Alternate',
        };
    }
}
