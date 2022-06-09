<?php

namespace GetCandy\Discounts\Managers;

use Illuminate\Support\Manager;

class DiscountConditionManager extends Manager
{
    public function createBasicDriver()
    {
        // dd(1);
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
        return 'basic';
    }
}
