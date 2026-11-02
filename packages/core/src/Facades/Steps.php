<?php

namespace Lunar\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Core\Enums\StepList;
use Lunar\Core\Manifests\StepManifest;
use Lunar\Core\Manifests\StepSequence;

/**
 * @method static StepSequence for(StepList $list)
 *
 * @see StepManifest
 */
class Steps extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return \Lunar\Core\Contracts\StepManifest::class;
    }
}
