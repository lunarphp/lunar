<?php

namespace Lunar\Core\Contracts\Actions\Fulfilment;

use Illuminate\Database\Eloquent\Collection;
use Lunar\Core\Models\Contracts\Fulfilment as FulfilmentContract;
use Lunar\Core\Models\Fulfilment;

interface MergesFulfilments
{
    /**
     * Fold pre-ship source fulfilments into the target.
     *
     * @param  Collection<int, FulfilmentContract>  $sources
     * @return Fulfilment the target fulfilment
     */
    public function execute(FulfilmentContract $target, Collection $sources): Fulfilment;
}
