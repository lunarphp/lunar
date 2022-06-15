<?php

namespace GetCandy\Discounts\Managers;

use GetCandy\Discounts\Drivers\Rules\Coupon;
use GetCandy\Discounts\Drivers\Conditions\Product;
use Illuminate\Support\Manager;

class DiscountRuleManager extends Manager
{
    public function createCouponDriver()
    {
        return $this->container->make(Coupon::class);
    }

    public function createProductDriver()
    {
        return $this->container->make(Product::class);
    }

    /**
     * Build a tax provider instance.
     *
     * @param  string  $provider
     * @return mixed
     */
    public function buildProvider($provider)
    {
        return $this->container->make($provider);
    }

    public function getDefaultDriver()
    {
        return 'coupon';
    }
}
