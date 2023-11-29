<?php

namespace Lunar\Base;

interface FieldType
{
    /**
     * Return the field type value.
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Set the value for the field type.
     *
     * @param  mixed  $value
     * @return void
     */
    public function setValue($value);

    /**
     * Return the config for the field type.
     */
    public function getConfig(): array;
}
