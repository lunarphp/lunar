<?php

namespace Lunar\Core\States\Order;

use Lunar\Core\Contracts\OrderStateConfig;
use Lunar\Core\Enums\OrderStateCategory;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class PaymentState extends State
{
    abstract public function label(): string;

    abstract public function category(): OrderStateCategory;

    public static function config(): StateConfig
    {
        $config = app(OrderStateConfig::class);

        $stateConfig = parent::config()
            ->default($config->defaultPaymentState())
            ->registerState($config->paymentStates());

        foreach ($config->paymentTransitions() as $from => $tos) {
            foreach ($tos as $to) {
                $stateConfig->allowTransition($from, $to);
            }
        }

        return $stateConfig;
    }
}
