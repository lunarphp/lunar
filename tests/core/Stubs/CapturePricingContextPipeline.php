<?php

namespace Lunar\Tests\Core\Stubs;

use Closure;
use Lunar\Core\Contracts\PricingManager;
use Lunar\Core\DataObjects\StorefrontContext;

class CapturePricingContextPipeline
{
    public static ?StorefrontContext $context = null;

    public static ?string $legacyCurrencyCode = null;

    public function handle(PricingManager $pricingManager, Closure $next)
    {
        static::$context = $pricingManager->getContext();
        static::$legacyCurrencyCode = $pricingManager->currency?->code;

        return $next($pricingManager);
    }
}
