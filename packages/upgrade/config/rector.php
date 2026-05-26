<?php

declare(strict_types=1);

use Lunar\Upgrade\Rector\Actions\RewriteActionRunCallRector;
use Lunar\Upgrade\Rector\LunarSetList;
use Lunar\Upgrade\Rector\Price\DropPriceValueAccessRector;
use Lunar\Upgrade\Rector\Price\RewritePriceDecimalCallRector;
use Lunar\Upgrade\Rector\Price\RewritePriceFormattedCallRector;
use Lunar\Upgrade\Rector\Price\RewriteUnitPriceFormatterCallRector;
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
    ->withConfiguredRule(DropPriceValueAccessRector::class, LunarSetList::V1_TO_V2_MONEY_ATTRIBUTES)
    ->withConfiguredRule(RewritePriceDecimalCallRector::class, LunarSetList::V1_TO_V2_MONEY_ATTRIBUTES)
    ->withConfiguredRule(RewritePriceFormattedCallRector::class, LunarSetList::V1_TO_V2_MONEY_ATTRIBUTES)
    ->withConfiguredRule(RewriteUnitPriceFormatterCallRector::class, LunarSetList::V1_TO_V2_UNIT_AWARE_ATTRIBUTES)
    ->withConfiguredRule(RewriteActionRunCallRector::class, LunarSetList::V1_TO_V2_ACTION_CONTRACTS)
    ->withRules(LunarSetList::V1_TO_V2);
