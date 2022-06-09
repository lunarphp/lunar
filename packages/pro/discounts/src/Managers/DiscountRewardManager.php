<?php

namespace GetCandy\Discounts\Managers;

use Illuminate\Support\Manager;

class DiscountRewardManager extends Manager
{
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
        return null;
    }
}
