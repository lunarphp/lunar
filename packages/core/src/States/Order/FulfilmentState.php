<?php

namespace Lunar\Core\States\Order;

use Lunar\Core\Contracts\OrderStateConfig;
use Lunar\Core\Enums\StateCategory;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class FulfilmentState extends State
{
    abstract public function label(): string;

    abstract public function category(): StateCategory;

    public static function config(): StateConfig
    {
        $config = app(OrderStateConfig::class);

        $stateConfig = parent::config()
            ->default($config->defaultFulfilmentState())
            ->registerState($config->fulfilmentStates());

        foreach ($config->fulfilmentTransitions() as $from => $tos) {
            foreach ($tos as $to) {
                $stateConfig->allowTransition($from, $to);
            }
        }

        return $stateConfig;
    }
}
