<?php

declare(strict_types=1);

use Lunar\Upgrade\Rector\Orders\RewriteOrderRefundCallRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([RewriteOrderRefundCallRector::class]);
