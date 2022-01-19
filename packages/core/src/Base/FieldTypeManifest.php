<?php

namespace GetCandy\Base;

use GetCandy\Exceptions\FieldTypes\FieldTypeMissingException;
use GetCandy\Exceptions\FieldTypes\InvalidFieldTypeException;
use GetCandy\FieldTypes\Dropdown;
use GetCandy\FieldTypes\ListField;
use GetCandy\FieldTypes\Number;
use GetCandy\FieldTypes\Text;
use GetCandy\FieldTypes\Toggle;
use GetCandy\FieldTypes\TranslatedText;

class FieldTypeManifest
{
    /**
     * The FieldTypes available in GetCandy.
     *
     * @var \Illuminate\Support\Collection
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
        ]);
    }

    /**
     * Add a FieldType into GetCandy.
     *
     * @param string $classname
     *
     * @return void
     */
    public function add($classname)
    {
        if (!class_exists($classname)) {
            throw new FieldTypeMissingException($classname);
        }

        if (!(app()->make($classname) instanceof FieldType)) {
            throw new InvalidFieldTypeException($classname);
        }

        $this->fieldTypes->push($classname);
    }

    /**
     * Return the fieldtypes.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getTypes()
    {
        return $this->fieldTypes->map(fn ($type) => app()->make($type));
    }
}
