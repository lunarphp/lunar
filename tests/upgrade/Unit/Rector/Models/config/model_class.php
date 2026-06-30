<?php

declare(strict_types=1);

use Lunar\Upgrade\Rector\Models\RewriteModelClassCallRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([RewriteModelClassCallRector::class]);
