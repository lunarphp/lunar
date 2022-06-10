<?php

namespace GetCandy\Discounts\Managers;

use GetCandy\Discounts\Drivers\Rewards\Percentage;
use Illuminate\Support\Manager;

class DiscountRewardManager extends Manager
{
    public function createPercentageDriver()
    {
        return $this->container->make(Percentage::class);
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
        return 'percentage';
    }
}
