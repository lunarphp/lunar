<?php

namespace Lunar\Core\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Lunar\Core\Models\Contracts\Fulfilment as FulfilmentContract;
use Lunar\Core\Models\Contracts\Order as OrderContract;
use Lunar\Core\Models\Fulfilment;

/**
 * Ergonomic entry point onto the fulfilment domain. Delegates to the swappable
 * `Actions\Fulfilment\*` seams — overriding an action binding takes effect
 * through this manager (and the `Fulfilments` facade) too.
 */
interface FulfilmentManager
{
    /**
     * @param  array<int|string, int>  $lines  [order_line_id => quantity]
     * @param  array<string, mixed>  $attributes
     */
    public function create(OrderContract $order, array $lines, array $attributes = []): Fulfilment;

    /**
     * @param  array<int|string, int>  $moves  [order_line_id => quantity to move out]
     */
    public function split(FulfilmentContract $fulfilment, array $moves): Fulfilment;

    /**
     * @param  Collection<int, FulfilmentContract>  $sources
     */
    public function merge(FulfilmentContract $target, Collection $sources): Fulfilment;

    /**
     * Move selected line quantities from one pre-ship fulfilment into another.
     *
     * @param  array<int|string, int>  $moves  [order_line_id => quantity to move]
     */
    public function move(FulfilmentContract $from, FulfilmentContract $to, array $moves): Fulfilment;

    /**
     * @param  array<string, mixed>  $tracking
     */
    public function ship(FulfilmentContract $fulfilment, array $tracking = []): Fulfilment;

    public function cancel(FulfilmentContract $fulfilment): Fulfilment;

    public function return(FulfilmentContract $fulfilment): Fulfilment;
}
