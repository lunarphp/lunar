<?php

namespace Lunar\Core\Managers;

use Illuminate\Database\Eloquent\Collection;
use Lunar\Core\Contracts\Actions\Fulfilment\AddsFulfilmentTracking;
use Lunar\Core\Contracts\Actions\Fulfilment\CancelsFulfilment;
use Lunar\Core\Contracts\Actions\Fulfilment\ChangesFulfilmentLocation;
use Lunar\Core\Contracts\Actions\Fulfilment\CreatesFulfilment;
use Lunar\Core\Contracts\Actions\Fulfilment\MergesFulfilments;
use Lunar\Core\Contracts\Actions\Fulfilment\MovesFulfilmentLines;
use Lunar\Core\Contracts\Actions\Fulfilment\ReturnsFulfilment;
use Lunar\Core\Contracts\Actions\Fulfilment\ShipsFulfilment;
use Lunar\Core\Contracts\Actions\Fulfilment\SplitsFulfilment;
use Lunar\Core\Contracts\FulfilmentManager as FulfilmentManagerContract;
use Lunar\Core\Models\Contracts\Fulfilment as FulfilmentContract;
use Lunar\Core\Models\Contracts\Order as OrderContract;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\FulfilmentTracking;

/**
 * Thin facade over the fulfilment actions. It carries no logic of its own —
 * it injects the action contracts and delegates, so swapping an action
 * binding still takes effect through `Facades\Fulfilments`.
 */
class FulfilmentManager implements FulfilmentManagerContract
{
    public function __construct(
        protected CreatesFulfilment $create,
        protected SplitsFulfilment $split,
        protected MergesFulfilments $merge,
        protected MovesFulfilmentLines $move,
        protected ShipsFulfilment $ship,
        protected CancelsFulfilment $cancel,
        protected ReturnsFulfilment $return,
        protected ChangesFulfilmentLocation $changeLocation,
        protected AddsFulfilmentTracking $addTracking,
    ) {}

    public function create(OrderContract $order, array $lines, array $attributes = []): Fulfilment
    {
        return $this->create->execute($order, $lines, $attributes);
    }

    public function split(FulfilmentContract $fulfilment, array $moves): Fulfilment
    {
        return $this->split->execute($fulfilment, $moves);
    }

    public function merge(FulfilmentContract $target, Collection $sources): Fulfilment
    {
        return $this->merge->execute($target, $sources);
    }

    public function move(FulfilmentContract $from, FulfilmentContract $to, array $moves): Fulfilment
    {
        return $this->move->execute($from, $to, $moves);
    }

    public function ship(FulfilmentContract $fulfilment, array $tracking = []): Fulfilment
    {
        return $this->ship->execute($fulfilment, $tracking);
    }

    public function cancel(FulfilmentContract $fulfilment): Fulfilment
    {
        return $this->cancel->execute($fulfilment);
    }

    public function return(FulfilmentContract $fulfilment): Fulfilment
    {
        return $this->return->execute($fulfilment);
    }

    public function changeLocation(FulfilmentContract $fulfilment, int $locationId): Fulfilment
    {
        return $this->changeLocation->execute($fulfilment, $locationId);
    }

    public function addTracking(FulfilmentContract $fulfilment, array $attributes): FulfilmentTracking
    {
        return $this->addTracking->execute($fulfilment, $attributes);
    }
}
