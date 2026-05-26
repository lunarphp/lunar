<?php

namespace Lunar\Core\Contracts;

use Illuminate\Support\Collection;

interface AttributeManifest
{
    /**
     * Add an attribute type.
     *
     * @param  string  $classname
     * @return void
     */
    public function addType($classname);

    /**
     * Return the attribute types.
     */
    public function getTypes(): Collection;

    /**
     * Return an attribute type by it's key.
     *
     * @param  string  $handle
     * @return string|null
     */
    public function getType($handle);
}
