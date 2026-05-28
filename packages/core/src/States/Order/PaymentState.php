<?php

namespace Lunar\Core\States\Order;

use Lunar\Core\Enums\StateCategory;
use Lunar\Core\States\Order\Payment\Authorized;
use Lunar\Core\States\Order\Payment\Captured;
use Lunar\Core\States\Order\Payment\Failed;
use Lunar\Core\States\Order\Payment\Pending;
use Lunar\Core\States\Order\Payment\Refunded;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class PaymentState extends State
{
    abstract public function label(): string;

    abstract public function category(): StateCategory;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Pending::class)
            ->registerState([
                Pending::class,
                Authorized::class,
                Captured::class,
                Failed::class,
                Refunded::class,
            ])
            ->allowTransition(Pending::class, Authorized::class)
            ->allowTransition(Pending::class, Captured::class)
            ->allowTransition(Pending::class, Failed::class)
            ->allowTransition(Authorized::class, Captured::class)
            ->allowTransition(Authorized::class, Failed::class)
            ->allowTransition(Captured::class, Refunded::class)
            ->allowTransition(Failed::class, Pending::class);
    }
}
