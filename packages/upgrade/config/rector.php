<?php

declare(strict_types=1);

use Lunar\Upgrade\Rector\LunarSetList;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        getcwd().'/app',
        getcwd().'/config',
        getcwd().'/database',
    ])
    ->withRules(LunarSetList::V1_TO_V2);
