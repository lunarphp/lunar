<?php

declare(strict_types=1);

use Lunar\Upgrade\Rector\LunarSetList;
use Lunar\Upgrade\Rector\Price\DropPriceValueAccessRector;
use Lunar\Upgrade\Rector\Price\RewritePriceDecimalCallRector;
use Lunar\Upgrade\Rector\Price\RewritePriceFormattedCallRector;
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector;
use Rector\Renaming\ValueObject\RenameProperty;

return RectorConfig::configure()
    ->withConfiguredRule(
        RenamePropertyRector::class,
        array_map(
            fn (array $rename): RenameProperty => new RenameProperty(...$rename),
            LunarSetList::V1_TO_V2_PROPERTY_RENAMES,
        ),
    )
    ->withConfiguredRule(DropPriceValueAccessRector::class, LunarSetList::V1_TO_V2_MONEY_ATTRIBUTES)
    ->withConfiguredRule(RewritePriceDecimalCallRector::class, LunarSetList::V1_TO_V2_MONEY_ATTRIBUTES)
    ->withConfiguredRule(RewritePriceFormattedCallRector::class, LunarSetList::V1_TO_V2_MONEY_ATTRIBUTES);
