<?php

declare(strict_types=1);

use Lunar\Upgrade\Rector\Pricing\RetypeFormatterStyleParamRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([RetypeFormatterStyleParamRector::class]);
