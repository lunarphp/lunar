<?php

namespace Lunar\Facades;

use Lunar\Base\FieldTypeManifestInterface;
use Illuminate\Support\Facades\Facade;

class FieldTypeManifest extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return FieldTypeManifestInterface::class;
    }
}
