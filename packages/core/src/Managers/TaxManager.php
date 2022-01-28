<?php

namespace GetCandy\Managers;

use GetCandy\Drivers\SystemTaxDriver;
use Illuminate\Support\Manager;

class TaxManager extends Manager
{
    public function createSystemDriver()
    {
        return $this->buildProvider(SystemTaxDriver::class);
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
        return config('getcandy.taxes.driver', 'system');
    }
}
