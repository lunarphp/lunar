<?php

namespace Lunar\Core\States\Order;

use Lunar\Core\Enums\StateCategory;
use Lunar\Core\States\Order\Fulfilment\Backordered;
use Lunar\Core\States\Order\Fulfilment\Delivered;
use Lunar\Core\States\Order\Fulfilment\PartiallyShipped;
use Lunar\Core\States\Order\Fulfilment\Processing;
use Lunar\Core\States\Order\Fulfilment\Returned;
use Lunar\Core\States\Order\Fulfilment\Shipped;
use Lunar\Core\States\Order\Fulfilment\Unfulfilled;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class FulfilmentState extends State
{
    abstract public function label(): string;

    abstract public function category(): StateCategory;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Unfulfilled::class)
            ->registerState([
                Unfulfilled::class,
                Backordered::class,
                Processing::class,
                PartiallyShipped::class,
                Shipped::class,
                Delivered::class,
                Returned::class,
            ])
            ->allowTransition(Unfulfilled::class, Processing::class)
            ->allowTransition(Unfulfilled::class, Backordered::class)
            ->allowTransition(Backordered::class, Processing::class)
            ->allowTransition(Processing::class, Shipped::class)
            ->allowTransition(Processing::class, PartiallyShipped::class)
            ->allowTransition(PartiallyShipped::class, Shipped::class)
            ->allowTransition(Shipped::class, Delivered::class)
            ->allowTransition(Shipped::class, Returned::class)
            ->allowTransition(Delivered::class, Returned::class);
    }
}
