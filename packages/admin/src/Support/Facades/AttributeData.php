<?php

namespace Lunar\Admin\Support\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static synthesizeLivewireProperties
 * @method static getFilamentComponent($field)
 * @method static registerFieldType(string $coreFieldType, string $panelFieldType)
 * @method static getFieldTypes(): Collection
 * @method static getConfigurationFields(string $type = null): array
 */
class AttributeData extends Facade
{
    /**
     * Return the facade class reference.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'lunar-attribute-data';
    }
}
