<?php

declare(strict_types=1);

use Lunar\Upgrade\Rector\Catalogue\RewriteTranslatedFieldCallRector;
use Lunar\Upgrade\Rector\LunarSetList;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withConfiguredRule(RewriteTranslatedFieldCallRector::class, LunarSetList::V1_TO_V2_TRANSLATED_FIELDS);
