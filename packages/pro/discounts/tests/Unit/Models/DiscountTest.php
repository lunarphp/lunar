<?php

namespace GetCandy\Discounts\Tests\Unit\Models;

use GetCandy\Discounts\Tests\TestCase;
use GetCandy\Models\CartAddress;
use GetCandy\Models\Country;
use GetCandy\Models\Currency;
use GetCandy\Models\TaxClass;
use GetCandy\Shipping\Facades\Shipping;
use GetCandy\Shipping\Models\ShippingMethod;
use GetCandy\Shipping\Models\ShippingZone;
use GetCandy\Shipping\Resolvers\ShippingZoneResolver;
use GetCandy\Shipping\Tests\TestUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group getcandy.discounts
 */
class DiscountTest extends TestCase
{
    use RefreshDatabase, TestUtils;

    /** @test */
    public function zones_method_uses_shipping_zone_resolver()
    {
        dd(1);
    }
}
