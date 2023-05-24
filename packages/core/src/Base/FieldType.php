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
     * Return the display label for the field type.
     */
    public function getLabel(): string;

    /**
     * Return the config for the field type.
     */
    public function getConfig(): array;

    /**
     * Return the reference to the view used in the settings.
     */
    public function getSettingsView(): string;

    /**
     * Return the view used in editing.
     */
    public function getView(): string;
}
