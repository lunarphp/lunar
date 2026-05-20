<?php

declare(strict_types=1);

use Lunar\Upgrade\Rector\LunarSetList;
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;

return RectorConfig::configure()
    ->withPaths([
        getcwd().'/app',
        getcwd().'/config',
        getcwd().'/database',
    ])
    ->withConfiguredRule(RenameClassRector::class, LunarSetList::V1_TO_V2_CLASS_RENAMES)
    ->withRules(LunarSetList::V1_TO_V2);
