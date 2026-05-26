<?php

declare(strict_types=1);

use Lunar\Upgrade\Rector\Actions\RewriteActionRunCallRector;
use Lunar\Upgrade\Rector\LunarSetList;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withConfiguredRule(RewriteActionRunCallRector::class, LunarSetList::V1_TO_V2_ACTION_CONTRACTS);
