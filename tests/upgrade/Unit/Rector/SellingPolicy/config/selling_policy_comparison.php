<?php

declare(strict_types=1);

use Lunar\Upgrade\Rector\Catalogue\RewriteSellingPolicyComparisonRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([RewriteSellingPolicyComparisonRector::class]);
