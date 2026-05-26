<?php

namespace Lunar\Core\Managers;

use Illuminate\Support\Manager;
use Lunar\Core\Contracts\TaxManager as TaxManagerContract;
use Lunar\Core\Drivers\SystemTaxDriver;

class TaxManager extends Manager implements TaxManagerContract
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
        return config('lunar.taxes.driver', 'system');
    }
}
