<?php

namespace Lunar\Admin\Support\Facades;

use Filament\Forms\Components\Component;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Lunar\Models\Attribute;

/**
 * @method static Component getFilamentComponent(Attribute $attribute)
 * @method static \Lunar\Admin\Support\Forms\AttributeData registerFieldType(string $coreFieldType, string $panelFieldType)
 * @method static Collection getFieldTypes()
 * @method static array mutateConfigurationForForm(string|null $type = null, array $configuration = [])
 * @method static array getConfigurationFields(string|null $type = null)
 * @method static bool canHaveDefaultValue(string|null $type = null)
 * @method static array getDefaultValueValidationRules(string|null $type = null, array $configuration = [])
 * @method static void synthesizeLivewireProperties()
 *
 * @see \Lunar\Admin\Support\Forms\AttributeData
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
