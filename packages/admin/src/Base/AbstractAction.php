<?php

namespace GetCandy\Hub\Base\ActivityLog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class AbstractAction
{
    abstract public function execute(Model $owner, Collection $groups);
}
