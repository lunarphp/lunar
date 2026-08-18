<?php

namespace Lunar\Panel\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Panel\PanelManager;

/**
 * @method static \Lunar\Panel\PanelManager section(\Lunar\Panel\Sections\Section $section)
 * @method static \Lunar\Panel\PanelManager extendSection(\Lunar\Panel\Sections\SectionExtension $extension)
 * @method static \Lunar\Panel\Navigation\NavigationRegistry navigation()
 * @method static \Lunar\Panel\Navigation\NavigationRegistry settingsNavigation()
 * @method static \Lunar\Panel\Slots\SlotRegistry slots()
 * @method static \Lunar\Panel\PanelManager extendTable(string $tableId, string $extensionClass)
 * @method static \Lunar\Panel\PanelManager registerRoutes(\Closure $callback)
 * @method static \Lunar\Panel\PanelManager translations(string ...$namespaces)
 * @method static string guard()
 * @method static string path()
 * @method static array availableLocales()
 *
 * @see PanelManager
 */
class Panel extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PanelManager::class;
    }
}
