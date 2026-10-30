<?php

declare(strict_types=1);

use Lunar\Upgrade\Rector\Shipping\RenameShippingOptionCollectArgRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([RenameShippingOptionCollectArgRector::class]);
