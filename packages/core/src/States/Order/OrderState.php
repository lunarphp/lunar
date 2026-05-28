<?php

namespace Lunar\Core\States\Order;

use Lunar\Core\States\Order\Order\AwaitingPayment;
use Lunar\Core\States\Order\Order\Backordered;
use Lunar\Core\States\Order\Order\Cancelled;
use Lunar\Core\States\Order\Order\Complete;
use Lunar\Core\States\Order\Order\InProcess;
use Lunar\Core\States\Order\Order\OnHold;
use Lunar\Core\States\Order\Order\PartiallyShipped;
use Lunar\Core\States\Order\Order\PaymentFailed;
use Lunar\Core\States\Order\Order\Refunded;
use Lunar\Core\States\Order\Order\Returned;
use Lunar\Core\States\Order\Order\Shipped;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class OrderState extends State
{
    abstract public function label(): string;

    public function isManualOverride(): bool
    {
        return false;
    }

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(AwaitingPayment::class)
            ->registerState([
                AwaitingPayment::class,
                PaymentFailed::class,
                Backordered::class,
                InProcess::class,
                PartiallyShipped::class,
                Shipped::class,
                Complete::class,
                Returned::class,
                Refunded::class,
                OnHold::class,
                Cancelled::class,
            ]);
    }
}
