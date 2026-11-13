<?php

declare(strict_types=1);

use Lunar\Upgrade\Rector\Shipping\RewriteShippingOptionCollectRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([RewriteShippingOptionCollectRector::class]);
