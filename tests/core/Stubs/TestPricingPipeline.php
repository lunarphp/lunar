<?php

namespace Lunar\Tests\Core\Stubs;

use Closure;
use Lunar\Core\Base\PricingManagerInterface;

class TestPricingPipeline
{
    public function handle(PricingManagerInterface $pricingManager, Closure $next)
    {
        $matchedPrice = $pricingManager->pricing->matched;

        $matchedPrice->price = 200;

        $pricingManager->pricing->matched = $matchedPrice;

        return $next($pricingManager);
    }
}
