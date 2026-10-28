<?php

namespace Lunar\Core\Exceptions;

use Lunar\Core\Enums\StepList;

class RemovedStepConfigException extends LunarException
{
    /**
     * @param  list<StepList>  $lists
     */
    public function __construct(string $key, array $lists)
    {
        $replacements = implode(', ', array_map(fn (StepList $list): string => "Steps::for(StepList::{$list->name})", $lists));

        parent::__construct(
            "The [{$key}] config key is no longer read. Step lists are registered in code: remove the key from your config ".
            "and make any changes from a service provider with {$replacements}. Run `php artisan lunar:upgrade` for the equivalent calls."
        );
    }
}
