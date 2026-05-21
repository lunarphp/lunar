<?php

declare(strict_types=1);

use Lunar\Upgrade\Rector\LunarSetList;
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;

return RectorConfig::configure()
    ->withPaths([
        getcwd().'/app',
        getcwd().'/config',
        getcwd().'/database',
    ])
    ->withConfiguredRule(RenameClassRector::class, LunarSetList::V1_TO_V2_CLASS_RENAMES)
    ->withConfiguredRule(
        RenameMethodRector::class,
        array_map(
            fn (array $rename): MethodCallRename => new MethodCallRename(...$rename),
            LunarSetList::V1_TO_V2_METHOD_RENAMES,
        ),
    )
    ->withRules(LunarSetList::V1_TO_V2);
