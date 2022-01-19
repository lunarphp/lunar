<?php

namespace GetCandy\Facades;

use GetCandy\Base\FieldTypeManifestInterface;
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
