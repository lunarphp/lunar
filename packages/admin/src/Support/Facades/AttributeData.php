<?php

namespace Lunar\Admin\Support\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void synthesizeLivewireProperties
 * @method static \Filament\Forms\Components\Component getFilamentComponent($field)
 * @method static void registerFieldType(string $coreFieldType, string $panelFieldType)
 * @method static \Illuminate\Support\Collection getFieldTypes(): Collection
 * @method static array getConfigurationFields(string $type = null): array
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
