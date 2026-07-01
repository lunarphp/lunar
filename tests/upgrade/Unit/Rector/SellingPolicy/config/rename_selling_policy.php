<?php

declare(strict_types=1);

use Lunar\Upgrade\Rector\LunarSetList;
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
    );
