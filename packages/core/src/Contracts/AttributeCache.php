<?php

namespace Lunar\Core\Contracts;

interface AttributeCache
{
    /**
     * Return the attribute id for a given handle, or null when unknown.
     */
    public function getIdForHandle(string $handle): ?int;

    /**
     * Return the attribute handle for a given id, or null when unknown.
     */
    public function getHandleForId(int $id): ?string;

    /**
     * Return the FieldType class for a given attribute id, or null when unknown.
     *
     * @return class-string<FieldType>|null
     */
    public function getFieldTypeClassForId(int $id): ?string;

    /**
     * Flush the cached attribute maps.
     */
    public function flush(): void;
}
