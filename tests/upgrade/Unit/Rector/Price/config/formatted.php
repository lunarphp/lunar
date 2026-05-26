<?php

declare(strict_types=1);

use Lunar\Upgrade\Rector\LunarSetList;
use Lunar\Upgrade\Rector\Price\RewritePriceFormattedCallRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withConfiguredRule(RewritePriceFormattedCallRector::class, LunarSetList::V1_TO_V2_MONEY_ATTRIBUTES);
