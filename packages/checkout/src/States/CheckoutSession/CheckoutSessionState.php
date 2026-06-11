<?php

namespace Lunar\Checkout\States\CheckoutSession;

use Lunar\Checkout\Contracts\CheckoutSessionStateConfig;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

/**
 * Lifecycle machine for a checkout attempt. Tracks the session — independent of
 * any order system — so the same machine serves the Lunar driver and any
 * non-Lunar driver. The catalogue + transition table live behind the swappable
 * {@see CheckoutSessionStateConfig} seam, mirroring `OrderState`.
 */
abstract class CheckoutSessionState extends State
{
    abstract public function label(): string;

    public static function config(): StateConfig
    {
        $config = app(CheckoutSessionStateConfig::class);

        $stateConfig = parent::config()
            ->default($config->defaultState())
            ->registerState($config->states());

        foreach ($config->transitions() as $from => $tos) {
            foreach ($tos as $to) {
                $stateConfig->allowTransition($from, $to);
            }
        }

        return $stateConfig;
    }
}
