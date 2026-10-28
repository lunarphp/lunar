<?php

namespace Lunar\Core\Exceptions;

use Lunar\Core\Enums\StepList;

class UnknownStepException extends LunarException
{
    public function __construct(StepList $list, string $anchor, string $step)
    {
        parent::__construct(
            "Cannot position [{$step}] against [{$anchor}] in the {$list->name} step list: [{$anchor}] is not in the list. ".
            'It may have been removed or replaced by another service provider.'
        );
    }
}
