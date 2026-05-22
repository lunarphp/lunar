<?php

namespace Lunar\Filament\Support\Facades;

use Filament\Forms\Components\Component;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Lunar\Core\Models\Attribute;

/**
 * @method static Component getFilamentComponent(Attribute $attribute)
 * @method static \Lunar\Filament\Support\Forms\AttributeData registerFieldType(string $coreFieldType, string $panelFieldType)
 * @method static Collection getFieldTypes()
 * @method static array mutateConfigurationForForm(string|null $type = null, array $configuration = [])
 * @method static array getConfigurationFields(string|null $type = null)
 * @method static void synthesizeLivewireProperties()
 *
 * @see \Lunar\Filament\Support\Forms\AttributeData
 */
class AttributeData extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'lunar-attribute-data';
    }
}
