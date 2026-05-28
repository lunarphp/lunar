<?php

namespace Lunar\Core\Contracts;

use Illuminate\Support\Collection;

interface FieldTypeManifest
{
    /**
     * Register a field type against a type string.
     *
     * @param  class-string<FieldType>  $class
     */
    public function add(string $type, string $class): void;

    /**
     * Remove a registered field type.
     */
    public function remove(string $type): void;

    /**
     * Return the field type class registered for the given type string.
     *
     * @return class-string<FieldType>|null
     */
    public function getType(string $type): ?string;

    /**
     * Return all registered field types, keyed by type string.
     *
     * @return Collection<string, class-string<FieldType>>
     */
    public function getTypes(): Collection;
}
