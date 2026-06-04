<?php

namespace Lunar\Core\States\Product;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class ProductState extends State
{
    abstract public function label(): string;

    public static function config(): StateConfig
    {
        // Product lifecycle has no domain rule against any transition —
        // a Draft can be archived without publishing, an Archived product
        // can be republished, etc. The state machine is here for type
        // safety / labels / cast consistency, not transition gating.
        return parent::config()
            ->default(Draft::class)
            ->allowTransition(Draft::class, Published::class)
            ->allowTransition(Draft::class, Archived::class)
            ->allowTransition(Published::class, Draft::class)
            ->allowTransition(Published::class, Archived::class)
            ->allowTransition(Archived::class, Draft::class)
            ->allowTransition(Archived::class, Published::class);
    }
}
