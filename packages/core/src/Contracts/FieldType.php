<?php

namespace Lunar\Core\Contracts;

use JsonSerializable;

interface FieldType extends JsonSerializable
{
    /**
     * Return the raw value of the field type.
     */
    public function getValue(): mixed;

    /**
     * Set the value of the field type.
     */
    public function setValue(mixed $value): void;

    /**
     * Return the validation config for the field type.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array;

    /**
     * Describe the configuration fields used to build the field type's config UI.
     *
     * @return array<int|string, mixed>
     */
    public function getConfigurationFields(): array;
}
