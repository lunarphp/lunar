<?php

namespace Lunar\Core\Contracts;

use Illuminate\Support\Collection;

interface FieldTypeManifest
{
    /**
     * Add an attribute type.
     *
     * @param  string  $classname
     * @return void
     */
    public function add($classname);

    /**
     * Return a collection of available fieldtypes.
     */
    public function getTypes(): Collection;
}
