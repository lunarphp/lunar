<?php

namespace Lunar\Core\FieldTypes;

use Illuminate\Support\Collection;
use Lunar\Core\Contracts\FieldTypeManifest;
use Lunar\Core\Exceptions\FieldTypes\FieldTypeMissingException;
use Lunar\Core\Exceptions\FieldTypes\InvalidFieldTypeException;

class Manifest implements FieldTypeManifest
{
    /**
     * The FieldTypes available in Lunar.
     *
     * @var Collection
     */
    protected $fieldTypes;

    public function __construct()
    {
        $this->fieldTypes = collect([
            Dropdown::class,
            ListField::class,
            Number::class,
            Text::class,
            Toggle::class,
            TranslatedText::class,
            YouTube::class,
            File::class,
        ]);
    }

    /**
     * Add a FieldType into Lunar.
     *
     * @param  string  $classname
     * @return void
     */
    public function add($classname)
    {
        if (! class_exists($classname)) {
            throw new FieldTypeMissingException($classname);
        }

        if (! (app()->make($classname) instanceof FieldType)) {
            throw new InvalidFieldTypeException($classname);
        }

        $this->fieldTypes->push($classname);
    }

    /**
     * Return the fieldtypes.
     */
    public function getTypes(): Collection
    {
        return $this->fieldTypes->map(fn ($type) => app()->make($type));
    }
}
