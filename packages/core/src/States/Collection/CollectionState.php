<?php

namespace Lunar\Core\States\Collection;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class CollectionState extends State
{
    abstract public function label(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Draft::class)
            ->allowTransition(Draft::class, Published::class)
            ->allowTransition(Published::class, Archived::class)
            ->allowTransition(Published::class, Draft::class)
            ->allowTransition(Archived::class, Draft::class);
    }
}
