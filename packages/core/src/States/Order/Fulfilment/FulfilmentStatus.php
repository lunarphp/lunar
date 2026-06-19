<?php

namespace Lunar\Core\States\Order\Fulfilment;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

/**
 * Derived order-level fulfilment status — a rollup over the order's
 * fulfillable physical lines and the quantities its `Fulfilment` records
 * cover (see `Actions\Orders\ResolveFulfilmentStatus`).
 *
 * Distinct from the per-fulfilment `States\Fulfilment\FulfilmentState`: this is
 * derived and unguarded (assigned directly by the resolver), where the
 * lifecycle machine is hand-driven and guarded. Hence the suffix — a derived
 * rollup is a `*Status` (matching its `*_status` column); a hand-driven,
 * guarded lifecycle machine is a `*State`.
 */
abstract class FulfilmentStatus extends State
{
    abstract public function label(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Unfulfilled::class)
            ->registerState([
                Unfulfilled::class,
                PartiallyFulfilled::class,
                Fulfilled::class,
                PartiallyReturned::class,
                Returned::class,
            ]);
    }
}
