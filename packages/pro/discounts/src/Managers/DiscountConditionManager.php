<?php

namespace GetCandy\Discounts\Managers;

use GetCandy\Discounts\Drivers\Conditions\Coupon;
use Illuminate\Support\Manager;

class DiscountConditionManager extends Manager
{
    public function createCouponDriver()
    {
        return $this->container->make(Coupon::class);
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
