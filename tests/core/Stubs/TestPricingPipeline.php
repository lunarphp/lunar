<?php

namespace Lunar\Tests\Core\Stubs;

use Closure;
use Lunar\Core\Contracts\PricingManager;

class TestPricingPipeline
{
    public function handle(PricingManager $pricingManager, Closure $next)
    {
        $matchedPrice = $pricingManager->pricing->matched;

        $matchedPrice->price = 200;

        $pricingManager->pricing->matched = $matchedPrice;

        return $next($pricingManager);
    }
}
