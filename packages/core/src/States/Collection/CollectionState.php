<?php

namespace Lunar\Core\States\Collection;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class CollectionState extends State
{
    abstract public function label(): string;

    public static function config(): StateConfig
    {
        // Collection lifecycle has no domain rule against any transition,
        // same as Product (see ProductState::config()). Full mesh.
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
