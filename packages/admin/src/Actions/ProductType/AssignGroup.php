<?php

namespace GetCandy\Hub\Actions\ProductType;

use GetCandy\Hub\Base\ActivityLog\AbstractAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class AssignGroup extends AbstractAction
{
    /**
     * Execute the action.
     *
     * @param  Model  $owner
     * @param  \Illuminate\Support\Collection  $attributes
     */
    public function execute(Model $owner, Collection $attributes)
    {
    }
}
