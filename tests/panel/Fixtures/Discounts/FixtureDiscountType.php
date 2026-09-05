<?php

namespace Lunar\Tests\Panel\Fixtures\Discounts;

use Lunar\Core\DiscountTypes\AbstractDiscountType;
use Lunar\Core\Models\Cart;

/**
 * A discount type registered from outside core, standing in for one shipped by
 * a third-party package. It never applies anything — the point is the panel
 * seam around it, not the pricing.
 */
class FixtureDiscountType extends AbstractDiscountType
{
    public function getName(): string
    {
        return 'Fixture discount';
    }

    public function apply(Cart $cart): Cart
    {
        return $cart;
    }
}
