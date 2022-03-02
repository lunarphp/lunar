<?php

namespace GetCandy\Managers;

use GetCandy\Base\PaymentTypeInterface;
use GetCandy\PaymentTypes\OfflinePayment;
use GetCandy\Exceptions\InvalidPaymentTypeException;
use Illuminate\Support\Manager;

class PaymentManager extends Manager
{
    public function createOfflineDriver()
    {
        return $this->buildProvider(OfflinePayment::class);
    }

    /**
     * Set the payment type driver.
     *
     * @param string $type
     * @return \GetCandy\Base\PaymentTypeInterface
     */
    public function type($type): PaymentTypeInterface
    {
        $driver = config("getcandy.payments.types.{$type}.driver");

        if (!$driver) {
            throw new InvalidPaymentTypeException(
                "Payment type \"{$type}\" doesn't have a supported driver"
            );
        }

        return $this->driver($driver);
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
        return config('getcandy.payments.default', 'offline');
    }
}
