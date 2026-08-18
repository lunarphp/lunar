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
     * Descriptors are renderer-agnostic so any admin UI can build the form:
     * `key` is the configuration array key the input binds to, `type` one of
     * `text`, `number`, `toggle`, `select` (with `options`), `tags` (list of
     * strings, optional `suggestions`) or `lookups` (label/value rows).
     *
     * @return array<int, array{key: string, type: string, label: string, hint?: string, suggestions?: array<int, string>, options?: array<int, array{label: string, value: string}>}>
     */
    public function getConfigurationFields(): array;
}
