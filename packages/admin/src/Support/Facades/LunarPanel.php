<?php

namespace Lunar\Admin\Support\Facades;

use Closure;
use Filament\Panel;
use Illuminate\Support\Facades\Facade;
use Lunar\Admin\LunarPanelManager;

/**
 * @method static LunarPanelManager register()
 * @method static LunarPanelManager panel(Closure $closure)
 * @method static Panel getPanel()
 * @method static LunarPanelManager forceTwoFactorAuth(bool $state = true)
 * @method static LunarPanelManager disableTwoFactorAuth()
 * @method static LunarPanelManager extensions(array $extensions)
 * @method static array getExtensions()
 * @method static array getResources()
 * @method static array getPages()
 * @method static array getWidgets()
 * @method static LunarPanelManager useRoleAsAdmin((array|string) $roleHandle)
 * @method static mixed callHook(string $class, object|null $caller, string $hookName, void ...$args)
 *
 * @see LunarPanelManager
 */
class LunarPanel extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'lunar-panel';
    }
}
