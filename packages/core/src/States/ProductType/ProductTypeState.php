<?php

namespace Lunar\Core\States\ProductType;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class ProductTypeState extends State
{
    abstract public function label(): string;

    public static function config(): StateConfig
    {
        // Product type lifecycle has no domain rule against any transition —
        // the state machine is here for type safety / labels / cast
        // consistency, not transition gating. Types default to active because
        // status only gates the product create flow (a draft type cannot be
        // chosen for new products); existing products are never affected.
        return parent::config()
            ->default(Active::class)
            ->allowTransition(Active::class, Draft::class)
            ->allowTransition(Draft::class, Active::class);
    }
}
