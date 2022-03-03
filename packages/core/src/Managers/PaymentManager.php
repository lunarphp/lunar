<?php

namespace GetCandy\Managers;

use GetCandy\Base\PaymentTypeInterface;
use GetCandy\Exceptions\InvalidPaymentTypeException;
use GetCandy\PaymentTypes\OfflinePayment;
use Illuminate\Support\Manager;
use Illuminate\Support\Str;
use InvalidArgumentException;

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
        $type = config("getcandy.payments.types.{$driver}");

        if (!isset($type['driver'])) {
            throw new InvalidPaymentTypeException(
                "Payment type \"{$type['driver']}\" doesn't have a supported driver"
            );
        }

        $driver = $type['driver'];

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

        if (!$driverInstance) {
            throw new InvalidArgumentException("Driver [$driver] not supported.");
        }

        return $driverInstance->setConfig($type);
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
        return config('getcandy.payments.default');
    }
}
