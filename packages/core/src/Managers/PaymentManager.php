<?php

namespace Lunar\Managers;

use Lunar\PaymentTypes\OfflinePayment;
use Illuminate\Support\Manager;
use Illuminate\Support\Str;

class PaymentManager extends Manager
{
    public function createOfflineDriver()
    {
        return $this->buildProvider(OfflinePayment::class);
    }

    /**
     * Create a new driver instance.
     *
     * @param  string  $driver
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    protected function createDriver($driver)
    {
        $originalDriver = $driver;

        $type = config("lunar.payments.types.{$driver}");

        $driver = $type['driver'] ?? $originalDriver;

        $driverInstance = null;

        // First, we will determine if a custom driver creator exists for the given driver and
        // if it does not we will check for a creator method for the driver. Custom creator
        // callbacks allow developers to build their own "drivers" easily using Closures.
        if (isset($this->customCreators[$driver])) {
            $driverInstance = $this->callCustomCreator($driver);
        } else {
            $method = 'create'.Str::studly($driver).'Driver';

            if (method_exists($this, $method)) {
                $driverInstance = $this->$method();
            }
        }

        if ($driverInstance) {
            return $driverInstance->setConfig($type ?? []);
        }

        return parent::createDriver($originalDriver);
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
        return config('lunar.payments.default');
    }
}
