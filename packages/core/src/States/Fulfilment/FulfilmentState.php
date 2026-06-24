<?php

namespace Lunar\Core\States\Fulfilment;

use Lunar\Core\Contracts\FulfilmentStateConfig;
use Lunar\Core\Enums\FulfilmentStateCategory;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

/**
 * The per-fulfilment lifecycle — a hand-driven machine a merchant
 * progresses (mark shipped, mark collected, mark provisioned). The state graph
 * itself is owned by the fulfilment's registered fulfilment method; this abstract
 * base reads the union catalogue from the bound `FulfilmentStateConfig`, and a
 * per-state {@see category()} keeps the order rollup method-agnostic.
 */
abstract class FulfilmentState extends State
{
    /**
     * The state's slug. Spatie reads this for its morph mapping (falling back
     * to the class name), but Lunar keys notification lookups on it directly,
     * so every concrete state must set it. Declared without a default so a
     * subclass that omits it fails loudly rather than keying on an empty value.
     */
    public static string $name;

    abstract public function label(): string;

    /**
     * The fixed rollup category this state belongs to. Per-method graphs vary
     * the states; the rollup, split/merge and return mechanics reason only over
     * these four categories.
     */
    abstract public function category(): FulfilmentStateCategory;

    public static function config(): StateConfig
    {
        $config = app(FulfilmentStateConfig::class);

        $stateConfig = parent::config()
            ->default($config->defaultFulfilmentState())
            ->registerState($config->fulfilmentStates());

        // Every transition is the union of all methods' transitions, so each is
        // registered with the one method-aware guard that restricts it to the
        // fulfilment's own method graph at runtime (see MethodAwareTransition).
        foreach ($config->fulfilmentTransitions() as $from => $tos) {
            foreach ($tos as $to) {
                $stateConfig->allowTransition($from, $to, MethodAwareTransition::class);
            }
        }

        return $stateConfig;
    }
}
