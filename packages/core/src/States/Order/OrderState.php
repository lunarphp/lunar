<?php

namespace Lunar\Core\States\Order;

use Lunar\Core\Contracts\OrderStateConfig;
use Lunar\Core\Enums\OrderStateCategory;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class OrderState extends State
{
    abstract public function label(): string;

    /**
     * The coarse category this headline state belongs to, used for admin
     * colour-coding and category-level filtering.
     */
    abstract public function category(): OrderStateCategory;

    public static function config(): StateConfig
    {
        $config = app(OrderStateConfig::class);

        $stateConfig = parent::config()
            ->default($config->defaultOrderState())
            ->registerState($config->orderStates());

        foreach ($config->orderTransitions() as $from => $tos) {
            foreach ($tos as $to) {
                $stateConfig->allowTransition($from, $to);
            }
        }

        return $stateConfig;
    }
}
