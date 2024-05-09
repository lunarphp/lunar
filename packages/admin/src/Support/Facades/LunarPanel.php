<?php

namespace Lunar\Admin\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Lunar\Admin\LunarPanelManager register()
 * @method static \Lunar\Admin\LunarPanelManager panel(\Closure $closure)
 * @method static \Filament\Panel getPanel()
 * @method static \Lunar\Admin\LunarPanelManager extensions(array $extensions)
 * @method static array<class-string<\Filament\Resources\Resource>> getResources()
 * @method static array<class-string<\Filament\Pages\Page>> getPages()
 * @method static array<class-string<\Filament\Widgets\Widget>> getWidgets()
 * @method static \Lunar\Admin\LunarPanelManager useRoleAsAdmin(string|array $roleHandle)
 * @method static mixed callHook(string $class, string $hookName, ...$args)
 *
 * @see \Lunar\Admin\LunarPanelManager
 */
class LunarPanel extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'lunar-panel';
    }
}
