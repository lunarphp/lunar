<?php

namespace Lunar\Core\States\Brand;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class BrandState extends State
{
    abstract public function label(): string;

    public static function config(): StateConfig
    {
        // Brand lifecycle has no domain rule against any transition — the
        // state machine is here for type safety / labels / cast consistency,
        // not transition gating. Brands default to active (unlike products)
        // because they are curation metadata rather than gated content.
        return parent::config()
            ->default(Active::class)
            ->allowTransition(Active::class, Draft::class)
            ->allowTransition(Draft::class, Active::class);
    }
}
