<?php

namespace Lunar\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Base\FieldTypeManifestInterface;

/**
 * @see \Lunar\Base\FieldTypeManifest
 */
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
