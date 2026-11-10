<?php

declare(strict_types=1);

use Lunar\Upgrade\Rector\Media\RetypeGetThumbnailImageRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([RetypeGetThumbnailImageRector::class]);
