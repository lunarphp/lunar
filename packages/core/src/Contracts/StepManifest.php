<?php

namespace Lunar\Core\Contracts;

use Lunar\Core\Enums\StepList;
use Lunar\Core\Manifests\StepSequence;

interface StepManifest
{
    /**
     * The sequence for a list, to change or read. Change it only from a service provider.
     */
    public function for(StepList $list): StepSequence;
}
