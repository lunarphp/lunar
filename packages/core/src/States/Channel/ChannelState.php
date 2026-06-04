<?php

namespace Lunar\Core\States\Channel;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class ChannelState extends State
{
    abstract public function label(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Active::class)
            ->allowTransition(Active::class, Inactive::class)
            ->allowTransition(Inactive::class, Active::class);
    }
}
