<?php

declare(strict_types=1);

use Lunar\Upgrade\Rector\LunarSetList;
use Lunar\Upgrade\Rector\Price\RewritePriceDecimalCallRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withConfiguredRule(RewritePriceDecimalCallRector::class, LunarSetList::V1_TO_V2_MONEY_ATTRIBUTES);
