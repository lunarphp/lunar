<?php

namespace Lunar\Core\Contracts\Actions\Fulfilment;

use Illuminate\Database\Eloquent\Collection;
use Lunar\Core\Models\Fulfilment;

interface MergesFulfilments
{
    /**
     * Fold pre-ship source fulfilments into the target.
     *
     * @param  Collection<int, Fulfilment>  $sources
     * @return Fulfilment the target fulfilment
     */
    public function execute(Fulfilment $target, Collection $sources): Fulfilment;
}
