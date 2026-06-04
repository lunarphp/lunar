<?php

namespace Lunar\Core\States\Fulfilment;

use Lunar\Core\Contracts\FulfilmentStateConfig;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

/**
 * The per-parcel fulfilment lifecycle — the one hand-driven machine in spec
 * 0022 (a merchant marks a parcel shipped). Mirrors the `OrderState` pattern:
 * an abstract base reading the bound `FulfilmentStateConfig`.
 */
abstract class FulfilmentState extends State
{
    abstract public function label(): string;

    public static function config(): StateConfig
    {
        $config = app(FulfilmentStateConfig::class);

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
