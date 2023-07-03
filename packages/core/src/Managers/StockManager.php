<?php

namespace Lunar\Managers;

use Illuminate\Support\Manager;
use Lunar\Drivers\NullStockDriver;
use Lunar\Drivers\SimpleStockDriver;

class StockManager extends Manager
{
    public function createSimpleDriver()
    {
        return $this->buildProvider(SimpleStockDriver::class);
    }

    public function createNullDriver()
    {
        return $this->buildProvider(NullStockDriver::class);
    }

    /**
     * Build a stock provider instance.
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
        return config('lunar.stock.driver', 'simple');
    }
}
