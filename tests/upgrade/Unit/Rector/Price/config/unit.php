<?php

declare(strict_types=1);

use Lunar\Upgrade\Rector\LunarSetList;
use Lunar\Upgrade\Rector\Price\RewriteUnitPriceFormatterCallRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withConfiguredRule(RewriteUnitPriceFormatterCallRector::class, LunarSetList::V1_TO_V2_UNIT_AWARE_ATTRIBUTES);
