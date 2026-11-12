<?php

declare(strict_types=1);

use Lunar\Upgrade\Rector\Discounts\AddCartParamToCouponValidationRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([AddCartParamToCouponValidationRector::class]);
