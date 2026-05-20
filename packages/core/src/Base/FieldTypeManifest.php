<?php

namespace Lunar\Core\Base;

use Illuminate\Support\Collection;
use Lunar\Core\Exceptions\FieldTypes\FieldTypeMissingException;
use Lunar\Core\Exceptions\FieldTypes\InvalidFieldTypeException;
use Lunar\Core\FieldTypes\Dropdown;
use Lunar\Core\FieldTypes\File;
use Lunar\Core\FieldTypes\ListField;
use Lunar\Core\FieldTypes\Number;
use Lunar\Core\FieldTypes\Text;
use Lunar\Core\FieldTypes\Toggle;
use Lunar\Core\FieldTypes\TranslatedText;
use Lunar\Core\FieldTypes\YouTube;

class FieldTypeManifest
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
     *
     * @return Collection
     */
    public function getTypes()
    {
        return $this->fieldTypes->map(fn ($type) => app()->make($type));
    }
}
