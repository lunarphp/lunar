<?php

namespace GetCandy\Base;

use Illuminate\Support\Collection;

interface FieldTypeManifestInterface
{
    /**
     * Add an attribute type.
     *
     * @param string $classname
     *
     * @return void
     */
    public function add($classname);

    /**
     * Return a collection of available fieldtypes.
     */
    public function getTypes(): Collection;
}
