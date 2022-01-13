<?php

namespace GetCandy\Base;

use GetCandy\FieldTypes\ListField;
use GetCandy\FieldTypes\Number;
use GetCandy\FieldTypes\Text;
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
            Text::class,
            TranslatedText::class,
            Number::class,
            ListField::class,
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
            //
        }

        if (!(app()->make($classname) instanceof FieldType)) {
            //
        }

        $this->fieldTypes->push($classname);
    }

    public function getTypes()
    {
        return $this->fieldTypes->map(fn ($type) => app()->make($type));
    }
}
