<?php

namespace Lunar\Core\Manifests;

use Illuminate\Support\Collection;
use Lunar\Core\Contracts\FieldType;
use Lunar\Core\Contracts\FieldTypeManifest as FieldTypeManifestContract;
use Lunar\Core\Enums\FieldTypeEnum;
use Lunar\Core\Exceptions\FieldTypes\FieldTypeMissingException;
use Lunar\Core\Exceptions\FieldTypes\InvalidFieldTypeException;

class FieldTypeManifest implements FieldTypeManifestContract
{
    /**
     * Map of type string => FieldType class.
     *
     * @var array<string, class-string<FieldType>>
     */
    protected array $fieldTypes = [];

    public function __construct()
    {
        foreach (FieldTypeEnum::cases() as $case) {
            $this->fieldTypes[$case->value] = $case->fieldTypeClass();
        }
    }

    public function add(string $type, string $class): void
    {
        if (! class_exists($class)) {
            throw new FieldTypeMissingException($class);
        }

        if (! is_subclass_of($class, FieldType::class) && ! in_array(FieldType::class, class_implements($class) ?: [])) {
            throw new InvalidFieldTypeException($class);
        }

        $this->fieldTypes[$type] = $class;
    }

    public function remove(string $type): void
    {
        unset($this->fieldTypes[$type]);
    }

    /**
     * @return class-string<FieldType>|null
     */
    public function getType(string $type): ?string
    {
        return $this->fieldTypes[$type] ?? null;
    }

    /**
     * @return Collection<string, class-string<FieldType>>
     */
    public function getTypes(): Collection
    {
        return collect($this->fieldTypes);
    }
}
