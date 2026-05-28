<?php

namespace Lunar\Core\FieldTypes;

use Lunar\Core\Contracts\FieldType;

abstract class AbstractFieldType implements FieldType
{
    protected mixed $value = null;

    public function __construct(mixed $value = null)
    {
        $this->setValue($value);
    }

    /**
     * The raw value that lands in the database.
     */
    public function jsonSerialize(): mixed
    {
        return $this->value;
    }

    public function __toString(): string
    {
        if (is_scalar($this->value)) {
            return (string) $this->value;
        }

        return '';
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig(): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigurationFields(): array
    {
        return [];
    }
}
